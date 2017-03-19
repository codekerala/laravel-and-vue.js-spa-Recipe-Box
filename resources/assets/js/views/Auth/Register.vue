<template>
    <form class="form" @submit.prevent="register">
        <h1 class="form__title">Create an Account</h1>
        <div class="form__group">
            <label>Name</label>
            <input type="text" class="form__control" v-model="form.name">
            <small class="error__control" v-if="error.name">{{error.name[0]}}</small>
        </div>
        <div class="form__group">
            <label>Email</label>
            <input type="text" class="form__control" v-model="form.email">
            <small class="error__control" v-if="error.email">{{error.email[0]}}</small>
        </div>
        <div class="form__group">
            <label>Password</label>
            <input type="password" class="form__control" v-model="form.password">
            <small class="error__control" v-if="error.password">{{error.password[0]}}</small>
        </div>
        <div class="form__group">
            <label>Confirm Password</label>
            <input type="password" class="form__control" v-model="form.password_confirmation">
        </div>
        <div class="form__group">
            <button :disabled="isProcessing" class="btn btn__primary">Register</button>
        </div>
    </form>
</template>
<script type="text/javascript">
    import Flash from '../../helpers/flash'
    import { post } from '../../helpers/api'
    export default {
        data() {
            return {
                form: {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: ''
                },
                error: {},
                isProcessing: false
            }
        },
        methods: {
            register() {
                this.isProcessing = true
                this.error = {}
                post('api/register', this.form)
                    .then((res) => {
                        if(res.data.registered) {
                            Flash.setSuccess('Congratulations! You have now successfully registered.')
                            this.$router.push('/login')
                        }
                        this.isProcessing = false
                    })
                    .catch((err) => {
                        if(err.response.status === 422) {
                            this.error = err.response.data
                        }
                        this.isProcessing = false
                    })
            }
        }
    }
</script>
