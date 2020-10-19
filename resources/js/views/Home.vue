<template>
    <div>
        <div class="float-right">
            <button @click="newCurrency()" class="btn btn-success">Создать</button>
        </div>

        <h1>Валюты</h1>

        <div v-if="error" class="alert alert-danger" role="alert">
            Ошибка при загрузке справочника.
        </div>

        <table v-else class="table table-striped">
            <div v-if="loading">Загрузка...</div>
            <thead>
            <tr>
                <th scope="col">Код</th>
                <th scope="col">Название</th>
                <th scope="col">Номинал</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="currency in currencies" :key="currency.id">
                <th><button @click="viewCurrency(currency)" class="btn btn-link">{{ currency.code}}</button></th>
                <td>{{ currency.name }}</td>
                <td>{{ currency.nominal }}</td>
                <td>
                    <button @click="editCurrency(currency)" class="btn btn-primary">Изменить</button>
                    <button @click="deleteCurrency(currency.id)" class="btn btn-danger">Удалить</button>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="editLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h5 v-show="!editMode" class="modal-title" id="editLabel">Добавить валюту</h5>
                        <h5 v-show="editMode" class="modal-title" id="editLabel">Редактировать валюту</h5>

                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="editMode ? updateCurrency() : createCurrency()">
                        <div class="modal-body">
                            <div v-show="!editMode" class="form-group">
                                <input v-model="form.code" type="text" name="code"
                                       placeholder="Код валюты"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('code') }" />
                                <has-error :form="form" field="code"></has-error>
                            </div>
                            <div class="form-group">
                                <input v-model="form.name" type="text" name="name"
                                       placeholder="Название"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }" />
                                <has-error :form="form" field="name"></has-error>
                            </div>
                            <div class="form-group">
                                <input v-model="form.nominal" type="text" name="nominal"
                                       placeholder="Номинал"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('nominal') }" />
                                <has-error :form="form" field="nominal"></has-error>
                            </div>
                            <div class="form-group">
                                <textarea v-model="form.description" name="description"
                                       placeholder="Комментарий"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('description') }"></textarea>
                                <has-error :form="form" field="description"></has-error>
                            </div>
                        </div>
                        <div class="modal-footer text-center">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Отмена</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalView" tabindex="-1" role="dialog" aria-labelledby="editLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <canvas id="rates-history"></canvas>
                    </div>
                    <div class="modal-footer text-center">
                        <button data-dismiss="modal" class="btn btn-primary">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Chart from 'chart.js';

    export default {
        data() {
            return {
                loading: true,
                error: false,
                editMode: false,
                currencies: [],
                chart: null,
                form: new Form({
                    id: '',
                    code : '',
                    name: '',
                    nominal: '1',
                    description: '',
                }),
            }
        },
        components: {
            Chart
        },
        methods: {
            loadCurrencies() {
                this.$Progress.start();
                axios.get("api/currency")
                    .then(data => (this.currencies = data.data))
                    .catch(error => {
                        console.error(error);
                        this.error = true;
                    })
                    .finally(() => {
                        this.loading = false;
                        this.$Progress.finish();
                    });
            },

            viewCurrency(currency) {
                console.log('> viewCurrency');
                this.$Progress.start();
                if (this.chart) {
                    this.chart.destroy();
                }
                $('#modalView').modal('show');

                axios.get('api/currency/' + currency.code)
                    .then(response => {
                        this.createChart({
                            type: 'line',
                            data: {
                                labels: response.data.data.dates,
                                datasets: [
                                    {
                                        label: 'Курс',
                                        data: response.data.data.values
                                    }
                                ]
                            }
                        });
                    })
                    .catch((data) => {
                        console.error(data);
                    })
                    .finally(() => {
                        this.$Progress.finish();
                    });
            },

            editCurrency(currency) {
                console.log('> editCurrency');
                this.form.clear();
                this.editMode = true;
                this.form.reset();
                $('#modalEdit').modal('show');
                this.form.fill(currency)
            },

            updateCurrency() {
                this.$Progress.start();

                this.form.put('api/currency/' + this.form.id)
                    .then(() => {
                        Toast.fire({
                            icon: 'success',
                            title: 'Валюта сохранена успешно.'
                        });
                        Fire.$emit('CurrenciesNeedReload');
                        $('#modalEdit').modal('hide');
                        this.$Progress.finish();
                    })
                    .catch((data) => {
                        console.error(data);
                    });
            },

            newCurrency() {
                console.log('> newCurrency');
                this.editMode = false;
                this.form.reset();
                $('#modalEdit').modal('show');
            },

            createCurrency(){
                this.$Progress.start();
                this.form.post('api/currency')
                    .then(() => {
                        Toast.fire({
                            icon: 'success',
                            title: 'Валюта сохранена успешно.'
                        });
                        Fire.$emit('CurrenciesNeedReload');
                        $('#modalEdit').modal('hide');
                        this.$Progress.finish();
                    })
                    .catch((data) => {
                        console.error(data);
                    });
            },

            deleteCurrency(id) {
                console.log('> deleteCurrency');
                Swal.fire({
                    title: 'Удаление валюты',
                    text: "Вы уверены, что хотите удалить эту валюту?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Да',
                    cancelButtonText: 'Нет'
                }).then((result) => {

                    if (result.value) {
                        this.$Progress.start();
                        this.form.delete('api/currency/' + id)
                            .then((response)=> {
                                Swal.fire(
                                    'Удалено!',
                                    'Валюта удалена успешно.',
                                    'success'
                                );
                            })
                            .catch(() => {
                                Swal.fire(
                                    'Ошибка',
                                    'Что-то пошло не так.',
                                    'error'
                                );
                            })
                            .finally(() => {
                                Fire.$emit('CurrenciesNeedReload');
                                this.$Progress.finish();
                            });
                    }
                })
            },

            createChart(chartData) {
                this.chart = new Chart(document.getElementById('rates-history'), {
                    type: chartData.type,
                    data: chartData.data,
                    options: chartData.options,
                });
            }
        },

        created() {
            this.loadCurrencies();

            Fire.$on('CurrenciesNeedReload', () => {
                this.loadCurrencies();
            });
        },

        beforeDestroy () {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    }
</script>
