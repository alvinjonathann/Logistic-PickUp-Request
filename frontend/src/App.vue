<template>
  <div id="app">
    <!-- Navbar -->
    <nav class="navbar">
      <h2 class="logo">AoL Web Dev</h2>

      <div v-if="isLoggedIn" class="nav-links">
        <router-link to="/dashboard">Dashboard</router-link>
        <button @click="logout" class="logout-btn">Logout</button>
      </div>

      <div v-else class="nav-links">
        <router-link to="/login">Login</router-link>
        <router-link to="/register">Register</router-link>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="content">
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem("token");
    },
  },
  methods: {
    logout() {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      this.$router.push("/login");
    },
  },
};
</script>

<style>
/* Layout */
#app {
  font-family: Arial, sans-serif;
  color: #333;
  min-height: 100vh;
  background: #f7f7f7;
}

/* Navbar */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 24px;
  background: #1e1e1e;
  color: white;
}

.nav-links {
  display: flex;
  gap: 18px;
  align-items: center;
}

.nav-links a {
  color: white;
  text-decoration: none;
  font-weight: bold;
}

.logout-btn {
  background: #ff4d4d;
  color: white;
  border: none;
  padding: 6px 12px;
  cursor: pointer;
  border-radius: 6px;
}

.logout-btn:hover {
  background: #e60000;
}

/* Page container */
.content {
  margin: 24px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  min-height: 400px;
  box-shadow: 0 0 12px rgba(0,0,0,0.1);
}
</style>
