<template>
	<div class="container">
		<div class="navbar">
			<div class="navbar__brand">
				<router-link to="/">Recipe Box</router-link>
			</div>
			<ul class="navbar__list">
				<li class="navbar__item"  v-if="guest">
					<router-link to="/login">LOGIN</router-link>
				</li>
				<li class="navbar__item"  v-if="guest">
					<router-link to="/register">REGISTER</router-link>
				</li>
				<li class="navbar__item"  v-if="auth">
					<router-link to="/recipes/create">CREATE RECIPE</router-link>
				</li>
				<li class="navbar__item"  v-if="auth">
					<a @click.stop="logout">LOGOUT</a>
				</li>
			</ul>
		</div>
		<div class="flash flash__error" v-if="flash.error">
			{{flash.error}}
		</div>
		<div class="flash flash__success" v-if="flash.success">
			{{flash.success}}
		</div>
		<router-view></router-view>
	</div>
</template>
<script type="text/javascript">
	import Auth from './store/auth'
	import Flash from './helpers/flash'
	import { post, interceptors } from './helpers/api'
	export default {
		created() {
			// global error http handler
			interceptors((err) => {
				if(err.response.status === 401) {
					Auth.remove()
					this.$router.push('/login')
				}

				if(err.response.status === 500) {
					Flash.setError(err.response.statusText)
				}

				if(err.response.status === 404) {
					this.$router.push('/not-found')
				}
			})
			Auth.initialize()
		},
		data() {
			return {
				authState: Auth.state,
				flash: Flash.state
			}
		},
		computed: {
			auth() {
				if(this.authState.api_token) {
					return true
				}
				return false
			},
			guest() {
				return !this.auth
			}
		},
		methods: {
			logout() {
				post('/api/logout')
				    .then((res) => {
				        if(res.data.done) {
				            // remove token
				            Auth.remove()
				            Flash.setSuccess('You have successfully logged out.')
				            this.$router.push('/login')
				        }
				    })
			}
		}
	}
</script>
