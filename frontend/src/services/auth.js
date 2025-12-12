import api from './api';
export async function login(payload){
  const r = await api.post('login', payload);
  localStorage.setItem('token', r.data.token);
  localStorage.setItem('user', JSON.stringify(r.data.user));
  return r.data.user;
}
export async function register(payload){
  const r = await api.post('register', payload);
  localStorage.setItem('token', r.data.token);
  localStorage.setItem('user', JSON.stringify(r.data.user));
  return r.data.user;
}
export function logout(){
  localStorage.removeItem('token');
  localStorage.removeItem('user');
}
