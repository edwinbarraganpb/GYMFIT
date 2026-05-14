/* GYMFIT - JS común: peticiones, sesión, logout, contacto */

const API = 'php';

async function api(path, opts = {}){
  const res = await fetch(`${API}/${path}`, {
    method: opts.method || 'GET',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: opts.body ? JSON.stringify(opts.body) : undefined,
  });
  let data = null;
  try { data = await res.json(); } catch(e){}
  if (!res.ok || !data?.ok) {
    throw new Error(data?.error || `Error ${res.status}`);
  }
  return data;
}

async function getMe(){
  try { const r = await api('me.php'); return r.user; }
  catch(e){ return null; }
}

async function logout(){
  try { await api('logout.php', { method:'POST' }); } catch(e){}
  location.href = 'login.html';
}

// Redirigir según rol
function redirectByRole(user){
  if (!user) { location.href = 'login.html'; return; }
  if (user.rol === 'entrenador') location.href = 'panel-entrenador.html';
  else location.href = 'panel-cliente.html';
}

// Guard de páginas privadas
async function requireRole(rol){
  const u = await getMe();
  if (!u) { location.href = 'login.html'; return null; }
  if (u.rol !== rol) { redirectByRole(u); return null; }
  return u;
}

// Toast simple Bootstrap
function toast(msg, type='success'){
  const id = 'gf-toast-'+Date.now();
  const html = `
    <div id="${id}" class="toast align-items-center text-bg-${type==='error'?'danger':type} border-0 position-fixed top-0 end-0 m-3" style="z-index:1080" role="alert">
      <div class="d-flex">
        <div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;
  document.body.insertAdjacentHTML('beforeend', html);
  const el = document.getElementById(id);
  const t = new bootstrap.Toast(el, { delay: 3500 });
  t.show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

// Wire del formulario de contacto (solo si existe)
document.addEventListener('DOMContentLoaded', () => {
  const f = document.getElementById('formContacto');
  if (f) f.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      await api('contacto.php', {
        method:'POST',
        body: {
          nombre: f.nombre.value.trim(),
          email: f.email.value.trim(),
          mensaje: f.mensaje.value.trim(),
        }
      });
      toast('¡Mensaje enviado! Pronto te contactaremos.');
      f.reset();
    } catch(err){ toast(err.message, 'error'); }
  });
});
