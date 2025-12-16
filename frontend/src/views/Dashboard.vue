<template>
  <div class="dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <div class="welcome-content">
        <h1>Kelola Pickup Anda</h1>
        <p>Buat, lihat, dan kelola semua request pickup dengan mudah</p>
      </div>
      <button class="btn-create" @click="openCreateModal"><span class="icon">+</span> Buat Request Baru</button>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
      <div class="tabs">
        <button :class="['tab', { active: activeTab === 'ongoing' }]" @click="activeTab = 'ongoing'"><span class="tab-icon">📋</span> Ongoing ({{ ongoingCount }})</button>
        <button :class="['tab', { active: activeTab === 'history' }]" @click="activeTab = 'history'"><span class="tab-icon">📜</span> History ({{ historyCount }})</button>
      </div>
    </div>

    <!-- Ongoing Tab -->
    <div v-if="activeTab === 'ongoing'" class="appointments-list">
      <div v-if="ongoing.length === 0" class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>Tidak ada request ongoing</h3>
        <p>Buat request pickup baru untuk memulai</p>
      </div>

      <div v-for="appt in ongoing" :key="appt.id" class="appointment-card ongoing">
        <div class="card-header">
          <div class="card-title">
            <h3>{{ appt.item_type }}</h3>
            <span class="badge waiting">Menunggu Pickup</span>
          </div>
          <div class="card-date">{{ formatDate(appt.pickup_date) }}</div>
        </div>

        <div class="card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="label">⏰ Waktu Pickup</span>
              <span class="value">{{ appt.pickup_time }}</span>
            </div>
            <div class="info-item">
              <span class="label">📦 Jumlah Barang</span>
              <span class="value">{{ appt.quantity }} item</span>
            </div>
            <div class="info-item">
              <span class="label">📏 Volume</span>
              <span class="value">{{ appt.volume }} m³</span>
            </div>
            <div class="info-item">
              <span class="label">🔖 ID</span>
              <span class="value small">{{ appt.id.substring(0, 8) }}...</span>
            </div>
          </div>
        </div>

        <div class="card-actions">
          <button class="btn btn-edit" @click="openEditModal(appt)">✏️ Edit Jadwal</button>
          <button class="btn btn-quantity" @click="openAddQuantityModal(appt)">➕ Tambah Barang</button>
          <button class="btn btn-cancel" @click="confirmCancel(appt)">❌ Batalkan</button>
        </div>
      </div>
    </div>

    <!-- History Tab -->
    <div v-if="activeTab === 'history'" class="appointments-list">
      <div v-if="history.length === 0" class="empty-state">
        <div class="empty-icon">📜</div>
        <h3>Tidak ada history</h3>
        <p>History pickup akan tampil di sini</p>
      </div>

      <div v-for="appt in history" :key="appt.id" class="appointment-card history">
        <div class="card-header">
          <div class="card-title">
            <h3>{{ appt.item_type }}</h3>
            <span :class="['badge', appt.status === 'cancelled' ? 'cancelled' : 'completed']">
              {{ formatStatus(appt.status) }}
            </span>
          </div>
          <div class="card-date">{{ formatDate(appt.pickup_date) }}</div>
        </div>

        <div class="card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="label">⏰ Waktu Pickup</span>
              <span class="value">{{ appt.pickup_time }}</span>
            </div>
            <div class="info-item">
              <span class="label">📦 Jumlah Barang</span>
              <span class="value">{{ appt.quantity }} item</span>
            </div>
            <div class="info-item">
              <span class="label">📏 Volume</span>
              <span class="value">{{ appt.volume }} m³</span>
            </div>
            <div class="info-item">
              <span class="label">🔖 ID</span>
              <span class="value small">{{ appt.id.substring(0, 8) }}...</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="modal-overlay" @click.self="closeCreateModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editingAppt ? "Edit Request Pickup" : "Buat Request Pickup Baru" }}</h2>
          <button class="modal-close" @click="closeCreateModal">✕</button>
        </div>

        <form @submit.prevent="handleCreateOrUpdate" class="form">
          <div class="form-group">
            <label>Tipe Barang *</label>
            <input v-model="formData.item_type" type="text" placeholder="Contoh: Elektronik, Pakaian, Peralatan" required class="input" />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Jumlah Barang *</label>
              <input v-model.number="formData.quantity" type="number" min="1" placeholder="Contoh: 5" required class="input" />
            </div>

            <div class="form-group">
              <label>Volume (m³) *</label>
              <input v-model.number="formData.volume" type="number" min="0" step="0.1" placeholder="Contoh: 2.5" required class="input" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Tanggal Pickup *</label>
              <input v-model="formData.pickup_date" type="date" required class="input" :min="getTodayDate()" />
            </div>

            <div class="form-group">
              <label>Jam Pickup *</label>
              <input v-model="formData.pickup_time" type="time" required class="input" />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary full">
              {{ editingAppt ? "Update Request" : "Buat Request" }}
            </button>
            <button type="button" @click="closeCreateModal" class="btn btn-secondary full">Batal</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Quantity Modal -->
    <div v-if="showQuantityModal" class="modal-overlay" @click.self="closeQuantityModal">
      <div class="modal small">
        <div class="modal-header">
          <h2>Tambah Jumlah Barang</h2>
          <button class="modal-close" @click="closeQuantityModal">✕</button>
        </div>

        <form @submit.prevent="handleAddQuantity" class="form">
          <div class="form-group">
            <label>Jumlah Saat Ini</label>
            <input :value="quantityModalData.quantity" disabled class="input disabled" />
          </div>

          <div class="form-group">
            <label>Jumlah Tambahan *</label>
            <input v-model.number="addQuantity" type="number" min="1" placeholder="Berapa banyak yang ditambahkan?" required class="input" />
          </div>

          <p class="form-note">
            Total akan menjadi: <strong>{{ quantityModalData.quantity + (addQuantity || 0) }} item</strong>
          </p>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary full">Tambah</button>
            <button type="button" @click="closeQuantityModal" class="btn btn-secondary full">Batal</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="modal-overlay" @click.self="closeConfirmModal">
      <div class="modal small">
        <div class="modal-header">
          <h2>Batalkan Request?</h2>
          <button class="modal-close" @click="closeConfirmModal">✕</button>
        </div>

        <div class="modal-body">
          <p>
            Apakah Anda yakin ingin membatalkan request pickup untuk <strong>{{ confirmData?.item_type }}</strong
            >?
          </p>
          <p class="text-muted">Request yang dibatalkan akan masuk ke history.</p>
        </div>

        <div class="form-actions">
          <button @click="handleCancel" class="btn btn-danger full">Ya, Batalkan</button>
          <button @click="closeConfirmModal" class="btn btn-secondary full">Tidak</button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="showToast" :class="['toast', toastType]">
      {{ toastMessage }}
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      activeTab: "ongoing",
      ongoing: [],
      history: [],

      showCreateModal: false,
      showQuantityModal: false,
      showConfirmModal: false,

      editingAppt: null,
      formData: {
        item_type: "",
        quantity: "",
        volume: "",
        pickup_date: "",
        pickup_time: "",
      },

      quantityModalData: null,
      addQuantity: "",

      confirmData: null,

      showToast: false,
      toastMessage: "",
      toastType: "success",
    };
  },

  computed: {
    ongoingCount() {
      return this.ongoing.length;
    },
    historyCount() {
      return this.history.length;
    },
  },

  async mounted() {
    const token = localStorage.getItem("token");
    if (!token) {
      this.$router.push("/login");
      return;
    }

    axios.defaults.headers.common["Authorization"] = "Bearer " + token;
    await this.loadAppointments();

    // Refresh data setiap 30 detik untuk auto-move ongoing ke history
    setInterval(() => {
      this.loadAppointments();
    }, 30000);
  },

  methods: {
    async loadAppointments() {
      try {
        const ongoingRes = await axios.get("http://localhost:8000/api/appointments?filter=ongoing");
        const historyRes = await axios.get("http://localhost:8000/api/appointments?filter=history");

        this.ongoing = ongoingRes.data || [];
        this.history = historyRes.data || [];
      } catch (error) {
        console.error("Error loading appointments:", error);
        this.showToastNotification("Gagal memuat data", "error");
      }
    },

    openCreateModal() {
      this.editingAppt = null;
      this.resetFormData();
      // Set default date to today
      const today = new Date().toISOString().split("T")[0];
      this.formData.pickup_date = today;
      this.showCreateModal = true;
    },

    closeCreateModal() {
      this.showCreateModal = false;
      this.editingAppt = null;
      this.resetFormData();
    },

    openEditModal(appt) {
      this.editingAppt = appt;
      this.formData = {
        item_type: appt.item_type,
        quantity: appt.quantity,
        volume: appt.volume,
        pickup_date: appt.pickup_date,
        pickup_time: appt.pickup_time,
      };
      this.showCreateModal = true;
    },

    openAddQuantityModal(appt) {
      this.quantityModalData = appt;
      this.addQuantity = "";
      this.showQuantityModal = true;
    },

    closeQuantityModal() {
      this.showQuantityModal = false;
      this.quantityModalData = null;
      this.addQuantity = "";
    },

    confirmCancel(appt) {
      this.confirmData = appt;
      this.showConfirmModal = true;
    },

    closeConfirmModal() {
      this.showConfirmModal = false;
      this.confirmData = null;
    },

    async handleCreateOrUpdate() {
      try {
        if (this.editingAppt) {
          // Update existing appointment
          await axios.put(`http://localhost:8000/api/appointments/${this.editingAppt.id}`, {
            item_type: this.formData.item_type,
            quantity: this.formData.quantity,
            volume: this.formData.volume,
            pickup_date: this.formData.pickup_date,
            pickup_time: this.formData.pickup_time,
          });
          this.showToastNotification("Request berhasil diupdate", "success");
        } else {
          // Create new appointment
          await axios.post("http://localhost:8000/api/appointments", {
            item_type: this.formData.item_type,
            quantity: this.formData.quantity,
            volume: this.formData.volume,
            pickup_date: this.formData.pickup_date,
            pickup_time: this.formData.pickup_time,
          });
          this.showToastNotification("Request berhasil dibuat", "success");
        }

        this.closeCreateModal();
        await this.loadAppointments();
      } catch (error) {
        console.error("Error creating/updating appointment:", error);
        // Log detailed error message
        const errorMessage = error.response?.data?.message || error.response?.data?.errors || error.message || "Gagal menyimpan request";
        console.error("Error details:", errorMessage);

        // Show more specific error message
        let userMessage = "Gagal menyimpan request";
        if (error.response?.status === 422) {
          const errors = error.response?.data?.errors;
          if (errors) {
            userMessage = Object.values(errors)[0]?.[0] || "Validasi gagal. Periksa input Anda.";
          }
        }
        this.showToastNotification(userMessage, "error");
      }
    },

    async handleAddQuantity() {
      try {
        const newQuantity = this.quantityModalData.quantity + this.addQuantity;
        await axios.put(`http://localhost:8000/api/appointments/${this.quantityModalData.id}`, { quantity: newQuantity });

        this.showToastNotification(`Jumlah barang berhasil ditambah menjadi ${newQuantity} item`, "success");
        this.closeQuantityModal();
        await this.loadAppointments();
      } catch (error) {
        console.error("Error:", error);
        this.showToastNotification("Gagal menambah jumlah barang", "error");
      }
    },

    async handleCancel() {
      try {
        await axios.delete(`http://localhost:8000/api/appointments/${this.confirmData.id}`);
        this.showToastNotification("Request berhasil dibatalkan", "success");
        this.closeConfirmModal();
        await this.loadAppointments();
      } catch (error) {
        console.error("Error:", error);
        this.showToastNotification("Gagal membatalkan request", "error");
      }
    },

    resetFormData() {
      this.formData = {
        item_type: "",
        quantity: "",
        volume: "",
        pickup_date: "",
        pickup_time: "",
      };
    },

    formatDate(dateString) {
      if (!dateString) return "";
      const date = new Date(dateString);
      return date.toLocaleDateString("id-ID", {
        weekday: "short",
        day: "numeric",
        month: "short",
        year: "numeric",
      });
    },

    formatStatus(status) {
      const statuses = {
        ongoing: "Ongoing",
        completed: "Selesai",
        cancelled: "Dibatalkan",
      };
      return statuses[status] || status;
    },

    showToastNotification(message, type = "success") {
      this.toastMessage = message;
      this.toastType = type;
      this.showToast = true;

      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    },

    getTodayDate() {
      return new Date().toISOString().split("T")[0];
    },
  },
};
</script>

<style scoped>
/* Dashboard Container */
.dashboard {
  width: 100%;
  color: #2c3e50;
}

/* Welcome Section */
.welcome-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  gap: 24px;
}

.welcome-content h1 {
  font-size: 32px;
  margin: 0 0 8px 0;
  color: #1a1a1a;
}

.welcome-content p {
  font-size: 16px;
  color: #666;
  margin: 0;
}

.btn-create {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-create:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-create .icon {
  font-size: 20px;
}

/* Tabs */
.tabs-container {
  margin-bottom: 24px;
  border-bottom: 2px solid #e0e0e0;
}

.tabs {
  display: flex;
  gap: 0;
}

.tab {
  background: none;
  border: none;
  padding: 16px 24px;
  font-size: 16px;
  font-weight: 600;
  color: #999;
  cursor: pointer;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.tab-icon {
  font-size: 18px;
}

.tab:hover {
  color: #667eea;
}

.tab.active {
  color: #667eea;
  border-bottom-color: #667eea;
}

/* Appointments List */
.appointments-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 24px;
  background: white;
  border-radius: 12px;
  border: 2px dashed #ddd;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 20px;
  color: #2c3e50;
  margin: 0 0 8px 0;
}

.empty-state p {
  color: #999;
  margin: 0;
}

/* Appointment Card */
.appointment-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border-left: 4px solid #667eea;
}

.appointment-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.appointment-card.history {
  border-left-color: #999;
  opacity: 0.8;
}

/* Card Header */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  gap: 16px;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.card-title h3 {
  font-size: 18px;
  margin: 0;
  color: #1a1a1a;
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge.waiting {
  background: #fef3c7;
  color: #b45309;
}

.badge.completed {
  background: #d1fae5;
  color: #065f46;
}

.badge.cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.card-date {
  color: #999;
  font-size: 14px;
  white-space: nowrap;
}

/* Card Body */
.card-body {
  margin-bottom: 16px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.info-item .label {
  font-size: 12px;
  color: #999;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-item .value {
  font-size: 16px;
  color: #2c3e50;
  font-weight: 500;
}

.info-item .value.small {
  font-family: "Courier New", monospace;
  font-size: 14px;
  color: #666;
}

/* Card Actions */
.card-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.btn {
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-edit {
  background: #e3f2fd;
  color: #1976d2;
}

.btn-edit:hover {
  background: #bbdefb;
}

.btn-quantity {
  background: #f3e5f5;
  color: #7b1fa2;
}

.btn-quantity:hover {
  background: #ede7f6;
}

.btn-cancel {
  background: #ffebee;
  color: #c62828;
}

.btn-cancel:hover {
  background: #ffcdd2;
}

.btn-secondary {
  background: #f0f0f0;
  color: #666;
}

.btn-secondary:hover {
  background: #e0e0e0;
}

.btn-danger {
  background: #ff6b6b;
  color: white;
}

.btn-danger:hover {
  background: #ee5a52;
}

.btn.full {
  width: 100%;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
}

.modal {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal.small {
  max-width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-header h2 {
  font-size: 20px;
  margin: 0;
  color: #1a1a1a;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #999;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.modal-close:hover {
  color: #333;
  background: #f5f5f5;
  border-radius: 6px;
}

.modal-body {
  padding: 24px;
}

.modal-body p {
  margin: 0 0 12px 0;
  color: #666;
  line-height: 1.6;
}

.modal-body p:last-child {
  margin-bottom: 0;
}

.text-muted {
  color: #999;
  font-size: 14px;
}

/* Form */
.form {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #2c3e50;
  font-size: 14px;
}

.input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  transition: all 0.3s ease;
  color: #2c3e50;
}

.input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input.disabled {
  background: #f5f5f5;
  cursor: not-allowed;
}

.form-note {
  background: #f0f4f8;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  color: #666;
  margin-bottom: 16px;
  border-left: 3px solid #667eea;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  padding: 16px 24px;
  border-radius: 8px;
  color: white;
  font-weight: 600;
  animation: slideIn 0.3s ease;
  z-index: 2000;
}

.toast.success {
  background: #10b981;
}

.toast.error {
  background: #ef4444;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-section {
    flex-direction: column;
    align-items: flex-start;
  }

  .btn-create {
    width: 100%;
    justify-content: center;
  }

  .welcome-content h1 {
    font-size: 24px;
  }

  .card-header {
    flex-direction: column;
  }

  .card-date {
    width: 100%;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .card-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .tab {
    padding: 12px 16px;
    font-size: 14px;
  }

  .toast {
    left: 16px;
    right: 16px;
    bottom: 16px;
  }
}
</style>
