import axios from 'axios'

const client = axios.create({
    baseURL: '/questionnaire/api', // Make this configurable later
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
})

export default {
    getQuestionnaires(params) {
        return client.get('/', { params })
    },
    getQuestionnaire(id) {
        return client.get(`/${id}`)
    },
    createQuestionnaire(data) {
        return client.post('/', data)
    },
    updateQuestionnaire(id, data) {
        return client.put(`/${id}`, data)
    },
    deleteQuestionnaire(id) {
        return client.delete(`/${id}`)
    },
    publishQuestionnaire(id) {
        return client.post(`/${id}/publish`)
    },
    closeQuestionnaire(id) {
        return client.post(`/${id}/close`)
    },
    getQuestionTypes() {
        return client.get('/question-types')
    },
    getResponses(id, params) {
        return client.get(`/${id}/responses`, { params })
    },
    getStatistics(id) {
        return client.get(`/${id}/statistics`)
    },
    submitResponse(id, data) {
        return client.post(`/public/${id}/submit`, data)
    }
}
