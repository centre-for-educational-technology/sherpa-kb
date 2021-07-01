<template>
    <div>
        <transition name="fade" mode="out-in">
            <div class="d-flex justify-content-center" v-if="!statistics">
                <b-spinner type="grow" label="Loading statistics data" small variant="secondary"></b-spinner>
            </div>

            <b-table
                striped
                hover
                :fields="fields"
                :items="languages"
                primary-key="id"
                thead-class="text-center"
                stacked="lg"
                v-if="statistics"
            >
                <template v-slot:cell(name)="data">
                    {{ data.value }}
                </template>

                <template v-slot:cell(questions)>
                    {{ statistics.questions.count }}
                </template>

                <template v-slot:cell(questions_translated)="data">
                    {{ questionTranslationsCount(data.item) }}
                </template>

                <template v-slot:cell(answers)>
                    {{ statistics.answers.count }}
                </template>

                <template v-slot:cell(answers_translated)="data">
                    {{ answerTranslationsCount(data.item) }}
                </template>

                <template v-slot:cell(available)="data">
                    {{ availableQuestionsCount(data.item) }}
                </template>
            </b-table>
        </transition>
    </div>
</template>

<script>
    import { mapState } from 'vuex'

    export default {
        computed: {
            ...mapState({
                languages: state => state.app.languages
            }),
            fields() {
                const fields = [
                    {
                        key: 'name',
                        label: 'Language',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'questions',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center', 'table-success']
                    },
                    {
                        key: 'questions_translated',
                        sortable: false,
                        tdClass: (value, key, item) => {
                            let classes = ['align-middle', 'text-center']

                            if (this.questionTranslationsCount(item) === this.statistics.questions.count) {
                                classes.push('table-success')
                            } else {
                                classes.push('table-warning')
                            }

                            return classes
                        }
                    },
                    {
                        key: 'answers',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center', 'table-success']
                    },
                    {
                        key: 'answers_translated',
                        sortable: false,
                        tdClass: (value, key, item) => {
                            let classes = ['align-middle', 'text-center']

                            if (this.answerTranslationsCount(item) === this.statistics.answers.count) {
                                classes.push('table-success')
                            } else {
                                classes.push('table-warning')
                            }

                            return classes
                        }
                    },
                    {
                        key: 'available',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    }
                ]

                return fields
            }
        },
        data() {
            return {
                statistics: null
            }
        },
        methods: {
            questionTranslationsCount(language) {
                const item = this.statistics.questions.translations.find(translation => language.code === translation.code)

                return item ? item.count : 0
            },
            answerTranslationsCount(language) {
                const item = this.statistics.answers.translations.find(translation => language.code === translation.code)

                return item ? item.count : 0
            },
            availableQuestionsCount(language) {
                const item = this.statistics.questions.available.find(available => language.code === available.code)

                return item ? item.count : 0
            }
        },
        created() {
            axios.get('/statistics')
            .then(response => {
                this.statistics = response.data
            })
            .catch(error => {
                console.error('Statistics loading:', error)
            })
        }
    }
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
