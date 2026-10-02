<?php
$pageTitle='User Messaging';
$subtitle='Send branded email and WhatsApp messages to everyone or a selected customer.';
$active='Broadcast';
require __DIR__.'/_layout.php';
$pdo=db();
$users=$pdo->query("SELECT id,name,email,phone,status FROM users WHERE role='user' ORDER BY name ASC")->fetchAll();
$history=$pdo->query("SELECT mc.id,mc.channel,mc.audience_type,mc.subject,mc.message,mc.created_at,u.name AS admin_name,
SUM(md.status='sent') AS sent_count,SUM(md.status='failed') AS failed_count,SUM(md.status='link_ready') AS link_count,COUNT(md.id) AS total_count
FROM message_campaigns mc LEFT JOIN message_deliveries md ON md.campaign_id=mc.id LEFT JOIN users u ON u.id=mc.admin_id
GROUP BY mc.id ORDER BY mc.id DESC LIMIT 12")->fetchAll();
?>
<div class="broadcast-hero">
  <div>
    <div class="eyebrow"><span class="live-dot"></span> COMMUNICATION CENTER</div>
    <h1>Reach your customers instantly</h1>
    <p>Send a polished announcement by email, WhatsApp, or both channels from one place.</p>
  </div>
  <div class="hero-stats">
    <div><strong><?=count($users)?></strong><span>Customers</span></div>
    <div><strong><?=count($history)?></strong><span>Recent campaigns</span></div>
  </div>
</div>

<div class="broadcast-grid">
  <section class="card composer-card">
    <div class="section-head">
      <div><div class="eyebrow">NEW CAMPAIGN</div><h2>Create message</h2></div>
      <span class="channel-badge"><span class="channel-dot email-dot"></span>Email <span class="plus">+</span> <span class="channel-dot wa-dot"></span> WhatsApp</span>
    </div>

    <form id="broadcastForm" class="form-grid">
      <div class="field">
        <label>Audience</label>
        <select class="select" name="audience" id="audience">
          <option value="all">All registered users</option>
          <option value="individual">One customer</option>
        </select>
      </div>
      <div class="field" id="userField" style="display:none">
        <label>Select customer</label>
        <select class="select" name="user_id" id="userId">
          <option value="">Choose customer</option>
          <?php foreach($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>"><?=ae($u['name'])?> — <?=ae($u['email'])?><?=!empty($u['phone'])?' — '.ae($u['phone']):''?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field full">
        <label>Delivery channels</label>
        <div class="channel-options">
          <label class="channel-option"><input type="radio" name="channel" value="email" checked><span class="channel-icon email-icon">@</span><span><b>Email</b><small>Send through SMTP</small></span></label>
          <label class="channel-option"><input type="radio" name="channel" value="whatsapp"><span class="channel-icon wa-icon">W</span><span><b>WhatsApp</b><small>Cloud API / ready link</small></span></label>
          <label class="channel-option selected"><input type="radio" name="channel" value="both"><span class="channel-icon both-icon">+</span><span><b>Both</b><small>Email + WhatsApp</small></span></label>
        </div>
      </div>
      <div class="field full" id="subjectField">
        <label>Email subject</label>
        <input class="input" name="subject" maxlength="255" placeholder="Swarnim Groups announcement">
      </div>
      <div class="field full">
        <div class="message-label"><label>Message</label><span id="charCount">0 / 5000</span></div>
        <textarea class="textarea message-box" name="message" id="messageBox" rows="9" maxlength="5000" placeholder="Write your announcement here..."></textarea>
      </div>
      <div class="field full"><div class="delivery-note"><strong>WhatsApp delivery</strong><span>Direct Cloud API delivery needs WhatsApp Business credentials and approved templates where Meta requires them. If API credentials are not configured, individual WhatsApp links are generated instead.</span></div></div>
      <div class="actions field full"><button class="btn primary send-btn" id="sendBtn" type="submit"><span class="send-icon">↗</span> Send Message</button><button class="btn" type="reset" id="resetBtn">Clear</button></div>
    </form>
    <div id="result" class="result-area"></div>
  </section>

  <aside class="card preview-card">
    <div class="section-head"><div><div class="eyebrow">LIVE PREVIEW</div><h2>Message preview</h2></div><span class="preview-status">READY</span></div>
    <div class="preview-mail">
      <div class="preview-brand"><img src="../data/Swarnim Logo.png" alt="Swarnim Groups"><div><strong>Swarnim Groups</strong><span id="previewChannel">Customer communication</span></div></div>
      <div class="preview-subject" id="previewSubject">Your Swarnim Groups message</div>
      <div class="preview-message" id="previewMessage">Your message preview will appear here.</div>
      <div class="preview-footer">This message was sent by Swarnim Groups.</div>
    </div>
    <div class="security-line"><span class="checkmark">✓</span> Admin-only sending with CSRF protection</div>
  </aside>
</div>

<section class="card users-card">
  <div class="section-head"><div><div class="eyebrow">CUSTOMER DIRECTORY</div><h2>Registered users</h2></div><span class="pill green"><?=count($users)?> users</span></div>
  <div class="user-search"><input class="input" id="userSearch" placeholder="Search name, email or phone..."></div>
  <div class="table-wrap"><table><thead><tr><th>User</th><th>Email</th><th>Phone</th><th>Status</th></tr></thead><tbody id="usersTable">
    <?php foreach($users as $u): ?><tr data-search="<?=ae(strtolower($u['name'].' '.$u['email'].' '.$u['phone']))?>"><td><div class="user-cell"><span class="mini-avatar"><?=ae(strtoupper(substr($u['name'],0,1)))?></span><span><?=ae($u['name'])?></span></div></td><td><?=ae($u['email'])?></td><td><?=ae($u['phone']?:'—')?></td><td><span class="pill <?=($u['status']??'active')==='active'?'green':'red'?>"><?=ae($u['status']??'active')?></span></td></tr><?php endforeach; ?>
  </tbody></table></div>
</section>

<section class="card history-card">
  <div class="section-head"><div><div class="eyebrow">DELIVERY LOG</div><h2>Recent campaigns</h2></div></div>
  <?php if(!$history): ?><div class="empty">No campaigns have been sent yet.</div><?php else: ?>
  <div class="campaign-list">
    <?php foreach($history as $h): ?>
      <div class="campaign-row">
        <div class="campaign-main"><div class="campaign-title"><span class="history-channel <?=ae($h['channel'])?>"><?=strtoupper(ae($h['channel']))?></span><?=ae($h['subject']?:'Untitled message')?></div><div class="campaign-meta"><?=ae($h['audience_type'])?> · <?=ae($h['admin_name']?:'Admin')?> · <?=date('d M Y, h:i A',strtotime($h['created_at']))?></div></div>
        <div class="campaign-results"><span class="pill green"><?= (int)$h['sent_count'] ?> sent</span><span class="pill red"><?= (int)$h['failed_count'] ?> failed</span><?php if((int)$h['link_count']): ?><span class="pill blue"><?= (int)$h['link_count'] ?> links</span><?php endif; ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<style>
.broadcast-hero{display:flex;justify-content:space-between;gap:22px;align-items:center;margin:0 0 20px;padding:24px 26px;border:1px solid rgba(244,197,66,.16);border-radius:24px;background:radial-gradient(circle at 85% 20%,rgba(117,183,255,.12),transparent 35%),linear-gradient(135deg,rgba(244,197,66,.08),rgba(255,255,255,.025));box-shadow:0 25px 80px rgba(0,0,0,.25);animation:rise .5s ease both}.broadcast-hero h1{font-size:34px;letter-spacing:-.04em;margin:5px 0 7px}.broadcast-hero p{margin:0;color:var(--muted);max-width:720px}.eyebrow{font-size:10px;letter-spacing:.16em;font-weight:900;color:#b9a76b}.live-dot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 14px var(--green);margin-right:7px}.hero-stats{display:flex;gap:10px}.hero-stats div{min-width:110px;padding:14px 16px;border:1px solid var(--line);border-radius:16px;background:rgba(255,255,255,.035);text-align:center}.hero-stats strong{display:block;font-size:24px}.hero-stats span{font-size:11px;color:var(--muted)}.broadcast-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr);gap:18px}.channel-badge,.preview-status{font-size:10px;font-weight:900;letter-spacing:.06em;padding:8px 11px;border-radius:999px;background:rgba(117,183,255,.08);color:#a9d2ff;border:1px solid rgba(117,183,255,.12)}.channel-dot{width:7px;height:7px;display:inline-block;border-radius:50%;margin-right:4px}.email-dot{background:#75b7ff}.wa-dot{background:#45dfa0}.plus{color:var(--muted);margin:0 3px}.channel-options{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.channel-option{position:relative;display:flex;align-items:center;gap:10px;padding:13px;border:1px solid var(--line);border-radius:15px;background:rgba(255,255,255,.025);cursor:pointer;transition:.22s}.channel-option:hover,.channel-option.selected{border-color:rgba(244,197,66,.45);background:rgba(244,197,66,.07);transform:translateY(-1px)}.channel-option input{position:absolute;opacity:0}.channel-option:has(input:checked){border-color:rgba(244,197,66,.55);box-shadow:0 0 0 2px rgba(244,197,66,.07)}.channel-icon{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;font-weight:900;background:#17202e;color:#fff}.email-icon{color:#9dceff}.wa-icon{color:#45dfa0}.both-icon{color:#ffe38b}.channel-option b,.channel-option small{display:block}.channel-option b{font-size:13px}.channel-option small{font-size:10px;color:var(--muted);margin-top:2px}.message-label{display:flex;justify-content:space-between;align-items:center}.message-label span{font-size:10px;color:var(--muted)}.message-box{resize:vertical;min-height:190px}.delivery-note{display:grid;gap:4px;padding:13px 14px;border:1px solid rgba(244,197,66,.14);background:rgba(244,197,66,.045);border-radius:14px;color:#e6d8a8;font-size:11px;line-height:1.5}.delivery-note strong{font-size:12px;color:#ffe38b}.send-btn{min-width:160px;justify-content:center}.send-icon{font-size:18px}.preview-card{min-height:100%;display:flex;flex-direction:column}.preview-mail{margin-top:5px;border:1px solid var(--line);border-radius:18px;background:#0a0f17;overflow:hidden;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}.preview-brand{display:flex;align-items:center;gap:10px;padding:16px;border-bottom:1px solid var(--line);background:linear-gradient(135deg,rgba(244,197,66,.09),rgba(117,183,255,.05))}.preview-brand img{width:38px;height:38px;object-fit:contain;border-radius:10px}.preview-brand strong,.preview-brand span{display:block}.preview-brand strong{font-size:13px}.preview-brand span{font-size:10px;color:var(--muted);margin-top:2px}.preview-subject{padding:18px 16px 6px;font-weight:800;font-size:16px}.preview-message{padding:8px 16px 26px;white-space:pre-wrap;min-height:180px;color:#cbd4e1;font-size:13px;line-height:1.7}.preview-footer{padding:13px 16px;border-top:1px solid var(--line);color:#6f7b8c;font-size:10px}.security-line{margin-top:auto;padding-top:16px;color:#8e9aaa;font-size:11px}.checkmark{display:inline-grid;place-items:center;width:18px;height:18px;border-radius:50%;background:rgba(69,223,160,.1);color:var(--green);margin-right:6px}.user-search{max-width:420px;margin-bottom:13px}.user-cell{display:flex;align-items:center;gap:9px}.mini-avatar{width:31px;height:31px;display:grid;place-items:center;border-radius:10px;background:linear-gradient(135deg,#f4c542,#72520e);color:#111;font-weight:900;font-size:11px}.campaign-list{display:grid;gap:8px}.campaign-row{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:14px;border:1px solid var(--line);border-radius:14px;background:rgba(255,255,255,.018)}.campaign-title{display:flex;align-items:center;gap:9px;font-weight:750;font-size:13px}.campaign-meta{color:var(--muted);font-size:10px;margin-top:5px}.campaign-results{display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end}.history-channel{font-size:9px;padding:4px 6px;border-radius:6px;background:rgba(117,183,255,.08);color:#9dceff}.history-channel.whatsapp{background:rgba(69,223,160,.08);color:var(--green)}.history-channel.both{background:rgba(244,197,66,.09);color:#ffe38b}.result-area{margin-top:14px}.result-card{padding:14px;border-radius:14px;border:1px solid rgba(69,223,160,.2);background:rgba(69,223,160,.055)}.result-card h3{margin:0 0 6px;font-size:14px}.result-card p{margin:0;color:#aeb9c9;font-size:12px}.result-links{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px}.loading{opacity:.6;pointer-events:none}.loading .send-icon{animation:spin .7s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}@media(max-width:1050px){.broadcast-grid{grid-template-columns:1fr}.preview-card{min-height:auto}.security-line{margin-top:14px}.channel-options{grid-template-columns:1fr}.broadcast-hero{align-items:flex-start;flex-direction:column}.hero-stats{width:100%}.hero-stats div{flex:1}}@media(max-width:680px){.broadcast-hero{padding:18px}.broadcast-hero h1{font-size:26px}.hero-stats{display:grid;grid-template-columns:1fr 1fr}.campaign-row{align-items:flex-start;flex-direction:column}.campaign-results{justify-content:flex-start}.channel-badge{display:none}}
</style>
<script>
const audience=document.querySelector('#audience');
const userField=document.querySelector('#userField');
const subjectField=document.querySelector('#subjectField');
const messageBox=document.querySelector('#messageBox');
const subjectInput=document.querySelector('[name="subject"]');
const charCount=document.querySelector('#charCount');
const previewMessage=document.querySelector('#previewMessage');
const previewSubject=document.querySelector('#previewSubject');
const previewChannel=document.querySelector('#previewChannel');
const form=document.querySelector('#broadcastForm');
const result=document.querySelector('#result');
const userSearch=document.querySelector('#userSearch');

audience.addEventListener('change',()=>userField.style.display=audience.value==='individual'?'grid':'none');

document.querySelectorAll('.channel-option input').forEach(input=>input.addEventListener('change',()=>{
  document.querySelectorAll('.channel-option').forEach(x=>x.classList.remove('selected'));
  input.closest('.channel-option').classList.add('selected');
  subjectField.style.display=input.value==='whatsapp'?'none':'grid';
  previewChannel.textContent=input.value==='both'?'Email + WhatsApp':input.value==='email'?'Email communication':'WhatsApp communication';
}));

function updatePreview(){
  const text=messageBox.value.trim();
  charCount.textContent=messageBox.value.length+' / 5000';
  previewMessage.textContent=text||'Your message preview will appear here.';
  previewSubject.textContent=subjectInput.value.trim()||'Your Swarnim Groups message';
}
messageBox.addEventListener('input',updatePreview); subjectInput.addEventListener('input',updatePreview);
userSearch.addEventListener('input',()=>{const q=userSearch.value.toLowerCase().trim();document.querySelectorAll('#usersTable tr').forEach(row=>row.style.display=(row.dataset.search||'').includes(q)?'':'none')});

document.querySelector('#resetBtn').addEventListener('click',()=>setTimeout(updatePreview,0));

form.addEventListener('submit',async e=>{
  e.preventDefault(); result.innerHTML='';
  const btn=document.querySelector('#sendBtn');btn.classList.add('loading');btn.innerHTML='<span class="send-icon">↻</span> Sending...';
  try{
    const fd=new FormData(form);fd.append('action','send-broadcast');
    const r=await fetch('api.php',{method:'POST',body:fd,headers:{'X-CSRF-Token':CSRF},credentials:'same-origin'});
    const j=await r.json();
    if(j.ok){
      result.innerHTML='<div class="result-card"><h3>Campaign processed</h3><p>'+escapeHtml(j.data.summary||j.message)+'</p>'+(j.data.links?.length?'<div class="result-links">'+j.data.links.map(x=>'<a class="btn blue" target="_blank" rel="noopener" href="'+x.url+'">Open WhatsApp · '+escapeHtml(x.name)+'</a>').join('')+'</div>':'')+'</div>';
      if(typeof toast==='function')toast(j.message,true);
    }else{result.innerHTML='<div class="pill red">'+escapeHtml(j.message)+'</div>';if(typeof toast==='function')toast(j.message,false)}
  }catch(err){result.innerHTML='<div class="pill red">Could not connect to the messaging server.</div>'}
  btn.classList.remove('loading');btn.innerHTML='<span class="send-icon">↗</span> Send Message';
});
function escapeHtml(v){return String(v).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]))}
</script>
<?php require __DIR__.'/_foot.php'; ?>
