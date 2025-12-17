<template>
  <div class="auth-wrapper">
    <div class="auth-card">
      <h2 class="title">Register</h2>

      <form @submit.prevent="register">
        <label>Nama</label>
        <input type="text" v-model="name" required class="input" />

        <label>Email</label>
        <input type="email" v-model="email" required class="input" />

        <label>Password</label>
        <input type="password" v-model="password" required class="input" />

        <label>Konfirmasi Password</label>
        <input type="password" v-model="password_confirmation" required class="input" />

        <button type="submit" class="btn-primary">Daftar</button>
      </form>

      <p class="switch-text">
        Sudah punya akun?
        <router-link to="/login" class="link">Login</router-link>
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
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      error: "",
    };
  },
  methods: {
    async register() {
      this.error = "";
      try {
        const response = await axios.post("http://localhost:8000/api/register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });

        // Auto-login: save token and redirect to dashboard
        if (response.data.token) {
          localStorage.setItem("token", response.data.token);
          localStorage.setItem("user", JSON.stringify(response.data.user));
          this.$router.push("/dashboard");
        } else {
          this.$router.push("/login");
        }
      } catch (err) {
        this.error = err.response?.data?.message || "Register gagal";
      }
    },
  },
};
</script>

<style scoped>
/* Pakai style login tadi agar seragam */
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
