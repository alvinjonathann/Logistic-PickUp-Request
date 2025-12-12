<template>
  <div class="appointments-page">
    <header class="page-header">
      <div>
        <h1 class="title">Logistic Appointment</h1>
        <p class="subtitle">Kelola appointment pick up Anda</p>
      </div>

      <div class="actions">
        <button class="btn create" @click="$router.push('/create')">+ Buat Request</button>
        <button class="btn ghost" @click="$emit('logout')">Logout</button>
      </div>
    </header>

    <nav class="tabs">
      <button :class="{ active: activeTab === 'ongoing' }" @click="activeTab = 'ongoing'">Ongoing ({{ ongoing.length }})</button>
      <button :class="{ active: activeTab === 'history' }" @click="activeTab = 'history'">History ({{ history.length }})</button>
    </nav>

    <section class="cards">
      <div v-if="activeTab === 'ongoing'">
        <div v-if="ongoing.length === 0" class="empty">No ongoing appointments.</div>
        <div class="card" v-for="a in ongoing" :key="a.id">
          <div class="card-head">
            <span class="status" :class="statusClass(a.status)">{{ a.status_label || a.status }}</span>
            <span class="date">{{ formatDate(a.scheduled_at) }}</span>
          </div>

          <h3 class="item-title">{{ a.item_type }}</h3>

          <div class="card-body">
            <div class="col">
              <div class="label">Jumlah:</div>
              <div class="value">{{ a.quantity }} item</div>

              <div class="label">Waktu:</div>
              <div class="value">{{ formatTime(a.scheduled_at) }}</div>
            </div>

            <div class="col">
              <div class="label">Volume:</div>
              <div class="value">{{ a.volume || '—' }} m³</div>

              <div class="label">Catatan:</div>
              <div class="value">{{ a.notes || '—' }}</div>
            </div>
          </div>

          <div class="card-actions">
            <button class="btn small" @click="edit(a)">Edit</button>
            <button class="btn danger small" @click="cancel(a.id)">Cancel</button>
          </div>
        </div>
      </div>

      <div v-if="activeTab === 'history'">
        <div v-if="history.length === 0" class="empty">No history.</div>
        <div class="card" v-for="h in history" :key="h.id">
          <div class="card-head">
            <span class="status" :class="statusClass(h.status)">{{ h.status_label || h.status }}</span>
            <span class="date">{{ formatDate(h.scheduled_at) }}</span>
          </div>

          <h3 class="item-title">{{ h.item_type }}</h3>

          <div class="card-body">
            <div class="col">
              <div class="label">Jumlah:</div>
              <div class="value">{{ h.quantity }} item</div>

              <div class="label">Waktu:</div>
              <div class="value">{{ formatTime(h.scheduled_at) }}</div>
            </div>

            <div class="col">
              <div class="label">Volume:</div>
              <div class="value">{{ h.volume || '—' }} m³</div>

              <div class="label">Catatan:</div>
              <div class="value">{{ h.notes || '—' }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import api from '@/services/api';
export default {
  data(){ return { ongoing:[], history:[], activeTab: 'history' } },
  async mounted(){ await this.load() },
  methods:{
    async load(){
      try{
        const r1 = await api.get('appointments?filter=ongoing'); this.ongoing = r1.data || [];
        const r2 = await api.get('appointments?filter=history'); this.history = r2.data || [];
      }catch(e){ console.error(e) }
    },
    async cancel(id){
      await api.delete(`appointments/${id}`);
      await this.load();
    },
    edit(item){
      this.$router.push({ name: 'EditAppointment', params: { id: item.id } });
    },
    formatDate(dt){
      if(!dt) return '';
      const d = new Date(dt);
      return d.toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    },
    formatTime(dt){
      if(!dt) return '';
      const d = new Date(dt);
      return d.toLocaleTimeString();
    },
    statusClass(status){
      if(!status) return '';
      const s = status.toLowerCase();
      if(s.includes('cancel') || s.includes('dibatalkan')) return 'canceled';
      if(s.includes('done') || s.includes('selesai') || s.includes('completed')) return 'done';
      return 'primary';
    }
  }
}
</script>

<style scoped>
:root{
  --bg: #f5f7f9;
  --card: #ffffff;
  --muted: #6b6b6b;
  --accent: linear-gradient(90deg,#6a5cff,#a85bff);
}
.appointments-page{ padding: 28px; background: var(--bg); min-height: calc(100vh - 40px); }
.page-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
.title{ margin:0; font-size:38px; letter-spacing:-0.5px; }
.subtitle{ margin:4px 0 0; color:var(--muted); }
.actions{ display:flex; gap:12px; align-items:center; }
.btn{ border:0; padding:10px 16px; border-radius:8px; cursor:pointer; transition: transform .18s ease, box-shadow .18s ease; font-weight:600 }
.btn.small{ padding:6px 10px; font-size:14px }
.btn.create{ background:var(--accent); color:white; box-shadow: 0 6px 18px rgba(106,92,255,0.14); }
.btn.create:hover{ transform: translateY(-4px) scale(1.02); box-shadow: 0 18px 30px rgba(106,92,255,0.18); }
.btn.ghost{ background: #6b7280; color:white }
.btn.ghost:hover{ transform: translateY(-3px) }
.btn.danger{ background:#ffd6d6; color:#b00000 }
.btn.danger:hover{ transform: translateY(-3px) }

.tabs{ display:flex; gap:18px; padding:12px 0 20px; border-bottom:1px solid rgba(0,0,0,0.06); margin-bottom:18px }
.tabs button{ background:transparent; border:0; padding:8px 14px; cursor:pointer; color:var(--muted); border-bottom:3px solid transparent }
.tabs button.active{ color:#4b4bff; border-color:#7a75ff }

.cards{ display:block; }
.card{ background:var(--card); border-radius:12px; padding:20px; margin-bottom:18px; box-shadow: 0 6px 18px rgba(18,24,40,0.04); transition: transform .22s ease, box-shadow .22s ease; }
.card:hover{ transform: translateY(-8px); box-shadow: 0 24px 46px rgba(18,24,40,0.12); }
.card-head{ display:flex; justify-content:space-between; align-items:center; margin-bottom:8px }
.status{ display:inline-block; padding:6px 12px; border-radius:16px; font-weight:700; font-size:13px }
.status.canceled{ background:#f9d7d7; color:#b03535 }
.status.done{ background:#dff6e6; color:#1a8a38 }
.status.primary{ background:#f1f3ff; color:#5b56d9 }
.date{ color:var(--muted); font-size:14px }
.item-title{ margin:6px 0 12px; font-size:24px }
.card-body{ display:flex; gap:40px; color:var(--muted); margin-bottom:14px }
.col{ min-width:200px }
.label{ font-size:13px; color:#8b8b8b; margin-bottom:6px }
.value{ font-weight:700; margin-bottom:12px; color:#222 }
.card-actions{ display:flex; gap:10px }
.empty{ padding:40px 20px; color:var(--muted); text-align:center }

/* Make buttons and cards respond smoothly when keyboard-focused */
.btn:focus, .card:focus{ outline: none; box-shadow: 0 6px 18px rgba(18,24,40,0.06); }
</style>
