<template>
  <div class="auth-wrapper">
    <div class="auth-card">
      <h2 class="title">Login</h2>

      <form @submit.prevent="login">
        <label>Email</label>
        <input type="email" v-model="email" required class="input" />

        <label>Password</label>
        <input type="password" v-model="password" required class="input" />

        <button type="submit" class="btn-primary">Login</button>
      </form>

      <p class="switch-text">
        Belum punya akun?
        <router-link to="/register" class="link">Daftar di sini</router-link>
      </p>

      <p class="error" v-if="error">{{ error }}</p>
    </div>
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
        this.error = err.response?.data?.message || "Login gagal";
      }
    },
  },
};
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa, #e9ecef);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.auth-card {
  background: white;
  width: 350px;
  padding: 32px;
  border-radius: 18px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
  text-align: center;
}

.title {
  margin-bottom: 24px;
}

label {
  display: block;
  font-size: 14px;
  text-align: left;
  margin-top: 10px;
}

.input {
  width: 100%;
  padding: 10px;
  margin-top: 4px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.btn-primary {
  width: 100%;
  margin-top: 20px;
  padding: 12px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(to right, #637aff, #8864ff);
  color: white;
  font-weight: bold;
  cursor: pointer;
}

.switch-text {
  margin-top: 16px;
}

.link {
  color: #5a38ff;
  font-weight: bold;
}

.error {
  margin-top: 12px;
  color: red;
}
</style>
