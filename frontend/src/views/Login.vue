<template>
  <div class="container">
    <h2>Login</h2>

    <form @submit.prevent="login">
      <label>Email</label>
      <input type="email" v-model="email" required />

      <label>Password</label>
      <input type="password" v-model="password" required />

      <button type="submit">Login</button>
    </form>

    <p>Belum punya akun? <router-link to="/register">Register</router-link></p>

    <p style="color:red">{{ error }}</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      email: "",
      password: "",
      error: "",
    };
  },
  methods: {
    async login() {
      this.error = "";

      try {
        const res = await axios.post("http://localhost:8000/api/login", {
          email: this.email,
          password: this.password,
        });

        localStorage.setItem("token", res.data.token);

        axios.defaults.headers.common["Authorization"] = "Bearer " + res.data.token;

        this.$router.push("/dashboard");
      } catch (err) {
        this.error = "Login gagal";
      }
    },
  },
};
</script>

<style>
.container {
  width: 300px;
  margin: 40px auto;
  display: flex;
  flex-direction: column;
}
input, button {
  margin-bottom: 10px;
  padding: 8px;
}
</style>
