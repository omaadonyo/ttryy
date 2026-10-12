{{-- Shared toast helper: dispatch Flux toasts from any page script via toast(text, variant, heading).
     Also surfaces server flashes (session status) and cross-redirect flashes (sessionStorage ttryy-flash). --}}
<script>
function toast(text, variant, heading){
  try{
    document.dispatchEvent(new CustomEvent('toast-show', { detail: {
      slots: { heading: heading || '', text: text || '' },
      dataset: { variant: variant || 'success' },
    }}));
  }catch(e){}
}
function toastStoredFlash(){
  try{
    const raw = sessionStorage.getItem('ttryy-flash');
    if(!raw) return;
    sessionStorage.removeItem('ttryy-flash');
    const j = JSON.parse(raw);
    if(j && j.text) setTimeout(() => toast(j.text, j.variant || 'success', j.heading || ''), 350);
  }catch(e){}
}
function flashToast(text, variant, heading){
  try{ sessionStorage.setItem('ttryy-flash', JSON.stringify({ text, variant: variant || 'success', heading: heading || '' })); }catch(e){}
}
/* Skippable onboarding hints (data-onboard-banner / data-onboard-dismiss) */
function initOnboard(){
  if(window.__onboardInit) return;
  window.__onboardInit = true;
  function stored(k){
    try{ return localStorage.getItem('ttryy-onboard-' + k) === '1'; }catch(e){ return false; }
  }
  document.querySelectorAll('[data-onboard-banner]').forEach(b => {
    b.classList.toggle('hidden', stored(b.dataset.onboardBanner));
  });
  document.querySelectorAll('[data-onboard-dismiss]').forEach(btn => btn.addEventListener('click', () => {
    try{ localStorage.setItem('ttryy-onboard-' + btn.dataset.onboardDismiss, '1'); }catch(e){}
    document.querySelector('[data-onboard-banner="' + btn.dataset.onboardDismiss + '"]')?.classList.add('hidden');
    if(typeof toast === 'function') toast('Got it — this hint will stay hidden.');
  }));
}
if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initOnboard);
else initOnboard();
@if(session('status'))
document.addEventListener('DOMContentLoaded', () => setTimeout(() => toast(@json(session('status')), 'success'), 350));
@endif
</script>
