<template>
  <div id="app">
    <!-- Navbar Modern -->
    <nav class="navbar">
      <div class="nav-container">
        <div class="nav-brand">
          <div class="logo-icon">📦</div>
          <h1 class="logo">AoL Logistics</h1>
        </div>

        <div v-if="isLoggedIn" class="nav-links">
          <router-link to="/dashboard" class="nav-link">Dashboard</router-link>
          <button @click="logout" class="nav-logout">Logout</button>
        </div>

        <div v-else class="nav-links">
          <router-link to="/login" class="nav-link">Login</router-link>
          <router-link to="/register" class="nav-link nav-register">Register</router-link>
        </div>
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
/* Reset & Global */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background: #f0f4f8;
  color: #2c3e50;
}

/* Layout */
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f0f4f8;
}

/* Navbar */
.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: white;
}

.logo-icon {
  font-size: 28px;
}

.logo {
  font-size: 22px;
  font-weight: 700;
  color: white;
  margin: 0;
  letter-spacing: -0.5px;
}

.nav-links {
  display: flex;
  gap: 24px;
  align-items: center;
}

.nav-link {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  font-weight: 500;
  font-size: 15px;
  transition: all 0.3s ease;
  padding: 8px 12px;
  border-radius: 6px;
}

.nav-link:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.nav-register {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 8px 16px;
}

.nav-register:hover {
  background: rgba(255, 255, 255, 0.3);
}

.nav-logout {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.nav-logout:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.5);
}

/* Page container */
.content {
  flex: 1;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 32px 24px;
}

/* Responsive */
@media (max-width: 768px) {
  .nav-container {
    padding: 12px 16px;
  }

  .logo {
    font-size: 18px;
  }

  .logo-icon {
    font-size: 24px;
  }

  .nav-links {
    gap: 12px;
  }

  .nav-link {
    font-size: 14px;
    padding: 6px 10px;
  }

  .content {
    padding: 24px 16px;
  }
}
</style>
