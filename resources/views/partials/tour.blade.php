{{-- Skippable guided tour for a page. Usage:
     @include('partials.tour', ['key' => 'marketing', 'auto' => true, 'steps' => [
         ['target' => '#tour-tabs', 'title' => '...', 'body' => '...'],
         ['target' => '#tour-ai', 'tab' => 'outreach', 'title' => '...', 'body' => '...'],
         ['target' => '[data-step="2"]', 'wiz' => 2, 'title' => '...', 'body' => '...'],
     ]])
     Restart any time via startTour_{key}(). Steps whose target is missing are skipped. --}}
<div id="tour-{{ $key }}-card" class="hidden fixed bottom-4 right-4 z-[70] w-[calc(100%-2rem)] max-w-sm card card-pad shadow-2xl">
    <div class="flex items-start justify-between gap-2">
        <p class="eyebrow">Guided tour · <span data-tour-count></span></p>
        <button data-tour-skip class="text-xs font-bold text-zinc-400 hover:text-zinc-700 dark:hover:text-white">Skip ✕</button>
    </div>
    <h3 data-tour-title class="section-title mt-1"></h3>
    <p data-tour-body class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"></p>
    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10"><div data-tour-bar class="h-1.5 rounded-full bg-brand-600 transition-all" style="width:0%"></div></div>
    <div class="mt-3 flex items-center justify-between">
        <button data-tour-back class="btn-ghost btn-sm">← Back</button>
        <button data-tour-next class="btn-brand btn-sm">Next →</button>
    </div>
</div>
<script>
(function(){
  const KEY = @json($key);
  const STEPS = @json($steps);
  const AUTO = @json($auto ?? true);
  const card = document.getElementById('tour-' + KEY + '-card');
  if(!card) return;
  let live = [], idx = 0;
  function paint(){
    const s = live[idx];
    if(!s) return;
    document.querySelectorAll('.tour-hl').forEach(e => e.classList.remove('tour-hl'));
    if(s.tab && typeof paintMtabs === 'function') paintMtabs(s.tab);
    if(s.wiz != null && typeof window.__wizGo === 'function') window.__wizGo(s.wiz);
    const el = document.querySelector(s.target);
    if(el){
      setTimeout(() => {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('tour-hl');
      }, s.tab != null || s.wiz != null ? 120 : 0);
    }
    card.querySelector('[data-tour-title]').textContent = s.title;
    card.querySelector('[data-tour-body]').textContent = s.body;
    card.querySelector('[data-tour-count]').textContent = (idx + 1) + ' of ' + live.length;
    card.querySelector('[data-tour-bar]').style.width = ((idx + 1) / live.length * 100) + '%';
    card.querySelector('[data-tour-back]').disabled = idx === 0;
    card.querySelector('[data-tour-next]').textContent = idx === live.length - 1 ? 'Finish ✓' : 'Next →';
  }
  function open(){
    live = STEPS.filter(s => document.querySelector(s.target));
    if(!live.length) return;
    idx = 0;
    card.classList.remove('hidden');
    paint();
  }
  function close(save){
    card.classList.add('hidden');
    document.querySelectorAll('.tour-hl').forEach(e => e.classList.remove('tour-hl'));
    if(save){ try{ localStorage.setItem('ttryy-tour-' + KEY, '1'); }catch(e){} }
    if(typeof toast === 'function' && save) toast('Tour skipped — restart it any time with Take tour.');
  }
  window['startTour_' + KEY] = function(){ try{ localStorage.removeItem('ttryy-tour-' + KEY); }catch(e){} open(); };
  card.querySelector('[data-tour-skip]').addEventListener('click', () => close(true));
  card.querySelector('[data-tour-back]').addEventListener('click', () => { if(idx > 0){ idx--; paint(); } });
  card.querySelector('[data-tour-next]').addEventListener('click', () => { if(idx < live.length - 1){ idx++; paint(); } else close(true); });
  document.addEventListener('keydown', e => { if(e.key === 'Escape' && !card.classList.contains('hidden')) close(true); });
  let auto = false;
  try{ auto = AUTO && localStorage.getItem('ttryy-tour-' + KEY) !== '1'; }catch(e){ auto = AUTO; }
  if(auto) setTimeout(open, 900);
})();
</script>
