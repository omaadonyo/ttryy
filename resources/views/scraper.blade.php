<x-layouts::app :title="__('Prospect scraper')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div>
            <flux:heading size="xl">Prospect scraper</flux:heading>
            <flux:text class="mt-1">Pull a sample of potential customers in your niche. Full contact details unlock for <strong>UGX 10,000</strong>.</flux:text>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] p-5 shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)]">
            <div class="grid sm:grid-cols-[1fr_1fr_auto] gap-2">
                <select id="dsc-niche" aria-label="Choose your niche" class="w-full rounded-full border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d]"></select>
                <input id="dsc-biz" type="text" placeholder="What does your business sell?" aria-label="What does your business sell" class="w-full rounded-xl border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 px-4 py-2.5 text-sm outline-none focus:border-[#9e005d] placeholder:text-zinc-400">
                <flux:button id="dsc-btn" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Scrape</flux:button>
            </div>
            <div id="dsc-status" class="hidden mt-4">
                <div class="flex justify-between text-xs font-semibold text-zinc-500"><span id="dsc-label">Starting…</span><span id="dsc-pct">0%</span></div>
                <div class="mt-1.5 h-2 bg-zinc-100 dark:bg-white/10 rounded-full overflow-hidden"><div id="dsc-bar" class="h-2 bg-[#9e005d] rounded-full transition-all duration-300" style="width:0%"></div></div>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[.04] shadow-[0_12px_32px_-12px_rgba(10,10,12,.18)] dark:shadow-[0_12px_32px_-12px_rgba(0,0,0,.7)] overflow-hidden">
            <div class="flex items-center gap-2.5 bg-zinc-50 dark:bg-white/5 border-b border-neutral-200 dark:border-neutral-700 px-4 py-3">
                <span class="text-[13px] font-semibold" id="dsc-file">prospects.pdf</span>
                <span id="dsc-count" class="ml-auto text-[11px] font-semibold text-zinc-500">0 records</span>
                <button id="dsc-save" class="hidden ml-2 shrink-0 text-[11px] font-bold text-white bg-[#9e005d] hover:bg-[#7e0049] rounded-full px-3 py-1 transition">Save contacts</button>
            </div>
            <div id="dsc-rows" class="divide-y divide-neutral-200 dark:divide-neutral-700">
                <p class="px-4 py-8 text-center text-sm text-zinc-400">Choose your niche and hit <strong>Scrape</strong> for a partially-visible sample.</p>
            </div>
            <div id="dsc-locked" class="hidden relative border-t border-neutral-200 dark:border-neutral-700">
                <div class="select-none blur-[3px] pointer-events-none px-4 py-3 space-y-2" id="dsc-blur" aria-hidden="true"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-white/70 dark:bg-zinc-950/70 px-6 text-center">
                    <p id="dsc-locked-text" class="text-sm font-bold">600+ full contacts locked</p>
                    <p class="text-xs text-zinc-500 max-w-xs">Names are visible above — phone numbers and contacts unlock with the full list.</p>
                    <div class="flex flex-col sm:flex-row gap-2 mt-1">
                        <flux:button id="dsc-unlock" variant="primary" class="!bg-[#9e005d] hover:!bg-[#7e0049]">Unlock full list · UGX 10,000</flux:button>
                        <flux:button :href="route('checkout')" variant="ghost" wire:navigate>Get the full package</flux:button>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-[11px] text-zinc-400">Demo uses sample data. Paid unlocks deliver real, niche-specific prospect lists.</p>
    </div>

<script>
const D_NICHES = [
 {code:'NGO',biz:['Foundation','Initiative','Trust','Relief'],name:'NGOs & Charities',tags:'ngo charity donor grant',prospects:['Grant makers','Foundations','Donors','Development agencies','UN organisations','Corporate foundations'],opps:['Grants','Funding opportunities','Fellowships','NGO partnerships']},
 {code:'CON',biz:['Builders','Construction','Estates','Contractors'],name:'Construction',tags:'construction building contractor',prospects:['Property developers','Real estate companies','Government institutions','NGOs','Schools','Hospitals','Hotels'],opps:['Construction tenders','Building projects','Renovation contracts']},
 {code:'IT',biz:['Systems','Tech','Digital','Software'],name:'IT & Software',tags:'it software tech digital',prospects:['SMEs','NGOs','Schools','Hospitals','Banks','SACCOs','Government institutions'],opps:['IT tenders','Website projects','Software contracts']},
 {code:'MKT',biz:['Media','Brands','Creative','Studios'],name:'Marketing & Advertising',tags:'marketing advertising branding',prospects:['Hotels','Restaurants','Banks','NGOs','Schools','Real estate'],opps:['Marketing contracts','Advertising contracts','Branding projects']},
 {code:'CLN',biz:['Cleaning','Hygiene','Facilities','Care'],name:'Cleaning & Facility Management',tags:'cleaning facility hygiene',prospects:['Offices','Hotels','Schools','Hospitals','Shopping malls'],opps:['Office cleaning contracts','Hotel contracts','Facility contracts']},
 {code:'SEC',biz:['Security','Guards','Protection','Surveillance'],name:'Security',tags:'security guard cctv',prospects:['Banks','Hotels','Schools','Hospitals','Factories','Warehouses'],opps:['Security tenders','Guarding contracts','CCTV projects']},
 {code:'CAT',biz:['Catering','Foods','Kitchen','Events'],name:'Catering & Food Services',tags:'catering food events',prospects:['Corporates','NGOs','Schools','Universities','Hospitals','Hotels'],opps:['Corporate catering','Event catering','Conference catering']},
 {code:'PRN',biz:['Print','Graphics','Signs','Press'],name:'Printing & Branding',tags:'printing branding signage',prospects:['Schools','NGOs','Corporates','Government agencies','Churches'],opps:['Annual reports','Brochures','Banners']},
 {code:'FUR',biz:['Furniture','Interiors','Woodworks','Fittings'],name:'Office Furniture',tags:'furniture office interior',prospects:['Companies','Schools','Hospitals','Hotels','NGOs','Banks'],opps:['Office furnishing','School furniture','Hotel furniture']},
 {code:'ACC',biz:['Associates','Auditors','Advisory','Partners'],name:'Accounting & Professional Services',tags:'accounting audit tax finance',prospects:['SMEs','NGOs','Schools','Churches','SACCOs','Startups'],opps:['Audit contracts','Accounting contracts','Bookkeeping']},
 {code:'LOG',biz:['Logistics','Freight','Movers','Cargo'],name:'Logistics & Transport',tags:'logistics transport delivery',prospects:['Importers','Exporters','Manufacturers','Retailers','NGOs'],opps:['Delivery contracts','Transport contracts','Fleet contracts']},
 {code:'AGR',biz:['Farms','Agro','Harvest','Foods'],name:'Agriculture & Agribusiness',tags:'agriculture farming agro',prospects:['Farmers','Commercial farms','Cooperatives','NGOs','Agro-processors'],opps:['Agri procurement','Seed supply','Equipment supply']},
 {code:'SOL',biz:['Solar','Energy','Power','Volt'],name:'Solar & Renewable Energy',tags:'solar energy power',prospects:['Schools','Hospitals','Hotels','Farms','Factories','NGOs'],opps:['Solar installations','Rural electrification','NGO projects']},
 {code:'MED',biz:['Medical','Pharma','Health','Labs'],name:'Medical Suppliers',tags:'medical health pharma',prospects:['Hospitals','Clinics','Pharmacies','NGOs','Laboratories'],opps:['Equipment tenders','Pharma procurement','Lab equipment']},
];
const D_FIRST=['Brian','Sarah','David','Grace','Moses','Anita','Peter','Ruth','James','Faith','Daniel','Sharon'];
const D_LAST=['Mukasa','Namono','Kato','Babirye','Ssemakula','Nakato','Wasswa','Achieng','Okello','Among','Kiggundu','Nalwoga'];
const D_A=['Nile','Victoria','Pearl','Savannah','Kampala','Rwenzori','Equator','Acacia','Baobab','Kololo'];
const D_B=['Ltd','Enterprises','Group','Uganda','Ventures','Solutions'];
const D_DIST=['Kampala','Wakiso','Entebbe','Jinja','Mbarara','Gulu','Mbale','Arua','Lira','Masaka'];
const drnd=a=>a[Math.floor(Math.random()*a.length)];
const dri=(a,b)=>a+Math.floor(Math.random()*(b-a+1));
const dphone=()=>{const d=()=>Math.floor(Math.random()*10);return `+256 7${dri(0,7)}${d()}${d()} ${d()}${d()}${d()} ${d()}${d()}${d()}`;};
const dmaskPhone=p=>p.slice(0,8)+'••• •••';
const dmaskName=n=>{const p=n.split(' ');return p[0]+' '+(p[1]?p[1][0]+'•••':'');};
const dRec=n=>({name:`${drnd(D_A)} ${drnd(n.biz)} ${drnd(D_B)}`,type:drnd(n.prospects),district:drnd(D_DIST),contact:`${drnd(D_FIRST)} ${drnd(D_LAST)}`,phone:dphone()});
const dSel=document.getElementById('dsc-niche');
D_NICHES.forEach(n=>{const o=document.createElement('option');o.value=n.code;o.textContent=n.name;dSel.appendChild(o);});
(function(){
  const wrap=document.createElement('div'); wrap.className='cs-wrap relative';
  dSel.parentNode.insertBefore(wrap,dSel); wrap.appendChild(dSel);
  dSel.classList.add('sr-only'); dSel.tabIndex=-1; dSel.setAttribute('aria-hidden','true');
  const btn=document.createElement('button');
  btn.type='button';
  btn.className='cs-btn w-full flex items-center justify-between gap-2 rounded-xl border border-neutral-300 dark:border-white/15 bg-white dark:bg-white/5 px-4 py-2.5 text-sm outline-none min-h-[46px]';
  btn.setAttribute('aria-expanded','false');
  btn.innerHTML='<span class="cs-label truncate">Choose your niche</span><svg class="w-4 h-4 shrink-0 text-zinc-400 transition-transform" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>';
  const list=document.createElement('div');
  list.className='cs-list hidden absolute z-30 left-0 right-0 mt-2 rounded-xl border border-neutral-200 dark:border-white/15 bg-white dark:bg-[#141416] shadow-2xl p-1.5 max-h-60 overflow-y-auto';
  list.setAttribute('role','listbox');
  function syncDsc(){
    const opt=dSel.options[dSel.selectedIndex];
    const label=wrap.querySelector('.cs-label');
    if(label&&opt) label.textContent=opt.textContent;
    wrap.querySelectorAll('.cs-list button').forEach(b=>{
      const tick=b.querySelector('.cs-tick');
      if(tick) tick.classList.toggle('hidden',b.dataset.value!==dSel.value);
    });
  }
  [...dSel.options].forEach(o=>{
    const item=document.createElement('button');
    item.type='button'; item.dataset.value=o.value; item.setAttribute('role','option');
    item.className='w-full flex items-center justify-between gap-2 text-left px-4 py-2 rounded-lg text-sm transition hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-800 dark:text-zinc-200';
    item.innerHTML='<span class="truncate">'+o.textContent+'</span><svg class="cs-tick w-4 h-4 shrink-0 text-[#9e005d] hidden" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>';
    item.addEventListener('click',()=>{ dSel.value=o.value; syncDsc(); closeDsc(); });
    list.appendChild(item);
  });
  function closeDsc(){ list.classList.add('hidden'); btn.setAttribute('aria-expanded','false'); }
  btn.addEventListener('click',()=>{ const open=list.classList.contains('hidden'); document.querySelectorAll('.cs-list').forEach(l=>l.classList.add('hidden')); if(open){ list.classList.remove('hidden'); btn.setAttribute('aria-expanded','true'); } });
  document.addEventListener('click',e=>{ if(!wrap.contains(e.target)) closeDsc(); });
  document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeDsc(); });
  const style=document.createElement('style');
  style.textContent='.cs-btn svg{transition:transform .25s ease;}.cs-btn[aria-expanded="true"] svg{transform:rotate(180deg);}.cs-wrap:focus-within .cs-btn{border-color:#9e005d;}';
  document.head.appendChild(style);
  wrap.appendChild(btn); wrap.appendChild(list);
  syncDsc();
})();
let dscBusy=false, dscTimers=[], dscNiche=null, dscTotal=0, dscRecords=[];
function dscRun(){
  if(dscBusy) return; dscBusy=true;
  dscTimers.forEach(clearTimeout); dscTimers=[];
  dscNiche=D_NICHES.find(n=>n.code===dSel.value)||D_NICHES[0];
  const rows=document.getElementById('dsc-rows'), status=document.getElementById('dsc-status'),
        bar=document.getElementById('dsc-bar'), label=document.getElementById('dsc-label'),
        pct=document.getElementById('dsc-pct'), locked=document.getElementById('dsc-locked'),
        btn=document.getElementById('dsc-btn');
  rows.innerHTML=''; locked.classList.add('hidden'); status.classList.remove('hidden'); dscRecords=[];
  document.getElementById('dsc-save').classList.add('hidden');
  btn.disabled=true; btn.classList.add('opacity-50');
  document.getElementById('dsc-file').textContent='prospects-'+dscNiche.code.toLowerCase()+'.pdf';
  document.getElementById('dsc-count').textContent='scanning…';
  [['Connecting to business directories…',25],['Scanning '+dscNiche.name.toLowerCase()+' sources…',55],['Masking contacts for preview…',80]].forEach(([t,p],i)=>{
    dscTimers.push(setTimeout(()=>{label.textContent=t;bar.style.width=p+'%';pct.textContent=p+'%';},i*700));
  });
  for(let i=0;i<3;i++){
    dscTimers.push(setTimeout(()=>{
      const r=dRec(dscNiche); dscRecords.push(r);
      const div=document.createElement('div');
      div.className='flex items-center justify-between gap-3 px-4 py-2.5';
      div.innerHTML=`<div class="min-w-0"><p class="text-[13px] font-bold truncate">${i+1}. ${r.name}</p>`
        +`<p class="text-[11px] text-zinc-500 truncate">${r.type} &middot; ${r.district}</p></div>`
        +`<div class="text-right shrink-0"><p class="text-[12px] font-semibold">${dmaskName(r.contact)}</p><p class="text-[11px] text-zinc-500 tracking-wider">${dmaskPhone(r.phone)}</p></div>`;
      rows.appendChild(div);
      document.getElementById('dsc-count').textContent=(i+1)+' of 3 free previews';
    },500+i*450));
  }
  dscTimers.push(setTimeout(()=>{
    dscTotal=dri(500,1000);
    document.getElementById('dsc-count').textContent='3 free + '+dscTotal+' locked';
    const blur=document.getElementById('dsc-blur'); blur.innerHTML='';
    for(let i=0;i<10;i++){const w1=dri(30,70),w2=dri(45,90);
      const row=document.createElement('div');row.className='space-y-1.5';
      row.innerHTML=`<div class="h-2.5 rounded-full bg-zinc-200 dark:bg-white/15" style="width:${w1}%"></div><div class="h-2 rounded-full bg-zinc-100 dark:bg-white/10" style="width:${w2}%"></div>`;
      blur.appendChild(row);}
    document.getElementById('dsc-locked-text').textContent=dscTotal+' full contacts locked in this PDF';
    locked.classList.remove('hidden');
    document.getElementById('dsc-save').classList.remove('hidden');
    label.textContent='Done — 3 free previews ready.';bar.style.width='100%';pct.textContent='100%';
    btn.disabled=false;btn.classList.remove('opacity-50');dscBusy=false;
  },500+3*450+500));
}
document.getElementById('dsc-btn').addEventListener('click',dscRun);
document.getElementById('dsc-save').addEventListener('click',async()=>{
  const btn=document.getElementById('dsc-save');
  if(!dscRecords.length||!dscNiche) return;
  btn.disabled=true; btn.textContent='Saving…';
  try{
    const token=document.querySelector('meta[name="csrf-token"]')?.content||'';
    const res=await fetch("{{ route('scraper.save') }}",{method:'POST',
      headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest'},
      body:JSON.stringify({niche:dscNiche.name,contacts:dscRecords})});
    if(!res.ok) throw new Error('save failed');
    const j=await res.json();
    btn.textContent='Saved '+j.saved+' ✓';
    setTimeout(()=>{window.location.href="{{ route('contacts.index') }}";},700);
  }catch(e){ btn.disabled=false; btn.textContent='Save contacts'; alert('Could not save. Please try again.'); }
});
document.getElementById('dsc-unlock').addEventListener('click',()=>{
  const msg='Hi Ttryy! I want to unlock the full prospect list (UGX 10,000) for '+(dscNiche?dscNiche.name:'my niche')+(dscTotal?' — sampled '+dscTotal+' locked contacts.':'');
  window.open('https://wa.me/256700000000?text='+encodeURIComponent(msg),'_blank');
});
</script>
</x-layouts::app>
