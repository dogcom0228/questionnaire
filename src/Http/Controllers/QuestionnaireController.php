<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Http\Requests\StoreQuestionnaireRequest;
use Liangjin0228\Questionnaire\Http\Requests\SubmitResponseRequest;
use Liangjin0228\Questionnaire\Http\Requests\UpdateQuestionnaireRequest;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Services\CloseQuestionnaireAction;
use Liangjin0228\Questionnaire\Services\CreateQuestionnaireAction;
use Liangjin0228\Questionnaire\Services\PublishQuestionnaireAction;
use Liangjin0228\Questionnaire\Services\SubmitResponseAction;
use Liangjin0228\Questionnaire\Services\UpdateQuestionnaireAction;

class QuestionnaireController extends BaseController
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $questionnaireRepository,
        protected ResponseRepositoryInterface $responseRepository,
        protected QuestionTypeRegistryInterface $questionTypeRegistry,
        protected CreateQuestionnaireAction $createAction,
        protected UpdateQuestionnaireAction $updateAction,
        protected PublishQuestionnaireAction $publishAction,
        protected CloseQuestionnaireAction $closeAction,
        protected SubmitResponseAction $submitAction
    ) {}

    /**
     * Display a listing of questionnaires.
     */
    public function index(Request $request): InertiaResponse
    {
        $this->setRootView();

        $filters = $request->only(['status', 'search']);

        // If authorization is enabled, filter by user
        if (config('questionnaire.features.authorization', true) && $request->user()) {
            $filters['user_id'] = $request->user()->getKey();
        }

        $questionnaires = $this->questionnaireRepository->paginate(
            config('questionnaire.pagination.per_page', 15),
            $filters
        );

        return Inertia::render($this->resolveComponent('Admin/Index'), [
            'questionnaires' => $questionnaires,
            'filters' => $filters,
            'statuses' => Questionnaire::getStatuses(),
        ]);
    }

    /**
     * Show the form for creating a new questionnaire.
     */
    public function create(): InertiaResponse
    {
        $this->setRootView();

        return Inertia::render($this->resolveComponent('Admin/Create'), [
            'questionTypes' => $this->questionTypeRegistry->toArray(),
            'duplicateStrategies' => $this->getDuplicateStrategies(),
        ]);
    }

    /**
     * Store a newly created questionnaire.
     */
    public function store(StoreQuestionnaireRequest $request): RedirectResponse
    {
        $questionnaire = $this->createAction->execute(
            $request->validated(),
            $request->user()?->getKey()
        );

        return redirect()
            ->route('questionnaire.admin.edit', $questionnaire)
            ->with('success', 'Questionnaire created successfully.');
    }

    /**
     * Display the specified questionnaire (admin view).
     */
    public function show(Questionnaire $questionnaire): InertiaResponse
    {
        $this->setRootView();
        $this->authorize('view', $questionnaire);

        $questionnaire->load('questions');

        return Inertia::render($this->resolveComponent('Admin/Show'), [
            'questionnaire' => $questionnaire,
            'statistics' => $this->responseRepository->getStatistics($questionnaire),
            'questionTypes' => $this->questionTypeRegistry->toArray(),
        ]);
    }

    /**
     * Show the form for editing the questionnaire.
     */
    public function edit(Questionnaire $questionnaire): InertiaResponse
    {
        $this->setRootView();
        $this->authorize('update', $questionnaire);

        $questionnaire->load('questions');

        return Inertia::render($this->resolveComponent('Admin/Edit'), [
            'questionnaire' => $questionnaire,
            'questionTypes' => $this->questionTypeRegistry->toArray(),
            'duplicateStrategies' => $this->getDuplicateStrategies(),
        ]);
    }

    /**
     * Update the specified questionnaire.
     */
    public function update(UpdateQuestionnaireRequest $request, Questionnaire $questionnaire): RedirectResponse
    {
        $this->updateAction->execute($questionnaire, $request->validated());

        return redirect()
            ->route('questionnaire.admin.edit', $questionnaire)
            ->with('success', 'Questionnaire updated successfully.');
    }

    /**
     * Remove the specified questionnaire.
     */
    public function destroy(Questionnaire $questionnaire): RedirectResponse
    {
        $this->authorize('delete', $questionnaire);

        $this->questionnaireRepository->delete($questionnaire);

        return redirect()
            ->route('questionnaire.admin.index')
            ->with('success', 'Questionnaire deleted successfully.');
    }

    /**
     * Publish the questionnaire.
     */
    public function publish(Questionnaire $questionnaire): RedirectResponse
    {
        $this->authorize('publish', $questionnaire);

        try {
            $this->publishAction->execute($questionnaire);
            return redirect()
                ->back()
                ->with('success', 'Questionnaire published successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Close the questionnaire.
     */
    public function close(Questionnaire $questionnaire): RedirectResponse
    {
        $this->authorize('close', $questionnaire);

        try {
            $this->closeAction->execute($questionnaire);
            return redirect()
                ->back()
                ->with('success', 'Questionnaire closed successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the public fill form.
     */
    public function fill(Request $request, Questionnaire $questionnaire): InertiaResponse|RedirectResponse
    {
        $this->setRootView();

        // Check if questionnaire is active
        if (!$questionnaire->is_accepting_responses) {
            return redirect()
                ->route('questionnaire.public.closed', $questionnaire)
                ->with('error', 'This questionnaire is not accepting responses.');
        }

        // Check authentication requirement
        if ($questionnaire->requires_auth && !$request->user()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please login to fill this questionnaire.');
        }

        $questionnaire->load('questions');

        return Inertia::render($this->resolveComponent('Public/Fill'), [
            'questionnaire' => $questionnaire,
            'questionTypes' => $this->questionTypeRegistry->toArray(),
        ]);
    }

    /**
     * Submit a response to the questionnaire.
     */
    public function submit(SubmitResponseRequest $request, Questionnaire $questionnaire): RedirectResponse
    {
        try {
            $this->submitAction->execute(
                $questionnaire,
                $request->validated(),
                $request
            );

            return redirect()
                ->route('questionnaire.public.thankyou', $questionnaire)
                ->with('success', 'Thank you for your response!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the thank you page after submission.
     */
    public function thankyou(Questionnaire $questionnaire): InertiaResponse
    {
        $this->setRootView();

        return Inertia::render($this->resolveComponent('Public/ThankYou'), [
            'questionnaire' => $questionnaire,
        ]);
    }

    /**
     * Display the closed questionnaire page.
     */
    public function closed(Questionnaire $questionnaire): InertiaResponse
    {
        $this->setRootView();

        return Inertia::render($this->resolveComponent('Public/Closed'), [
            'questionnaire' => $questionnaire,
        ]);
    }

    /**
     * Display responses for a questionnaire.
     */
    public function responses(Questionnaire $questionnaire): InertiaResponse
    {
        $this->setRootView();
        $this->authorize('viewResponses', $questionnaire);

        $responses = $this->responseRepository->paginateForQuestionnaire(
            $questionnaire,
            config('questionnaire.pagination.per_page', 15)
        );

        return Inertia::render($this->resolveComponent('Admin/Responses'), [
            'questionnaire' => $questionnaire,
            'responses' => $responses,
            'questionTypes' => $this->questionTypeRegistry->toArray(),
        ]);
    }

    /**
     * Set the Inertia root view.
     */
    protected function setRootView(): void
    {
        $rootView = config('questionnaire.ui.root_view', 'questionnaire::app');
        Inertia::setRootView($rootView);
    }

    /**
     * Resolve component path (allows host app to override).
     */
    protected function resolveComponent(string $component): string
    {
        $prefix = config('questionnaire.ui.component_prefix', 'Questionnaire/');
        return $prefix . $component;
    }

    /**
     * Get available duplicate submission strategies.
     *
     * @return array<string, string>
     */
    protected function getDuplicateStrategies(): array
    {
        return [
            'allow_multiple' => 'Allow Multiple Submissions',
            'one_per_user' => 'One Per User',
            'one_per_session' => 'One Per Session',
            'one_per_ip' => 'One Per IP Address',
        ];
    }

    /**
     * Authorize an action (wrapper for policy).
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorize(string $ability, $model): void
    {
        if (!config('questionnaire.features.authorization', true)) {
            return;
        }

        if (!request()->user()?->can($ability, $model)) {
            abort(403);
        }
    }
}
