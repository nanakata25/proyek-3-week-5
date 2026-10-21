const {chromium}=require('./playwright-runtime.cjs');
const {chromeOptions}=require('./browser-options.cjs');
const {root,php,env}=require('./modul5-runtime.cjs');
const fs=require('node:fs/promises'),syncfs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{execFileSync}=require('node:child_process');
const base='http://127.0.0.1:8015',dir=path.join(root,'evidence/modul-5'),shots=path.join(dir,'screenshots');
const result={date:'2026-10-21',database:'SQLite dummy modul5-demo.sqlite',scenarios:[]};
const snapshot=()=>JSON.parse(execFileSync(php,[path.join(root,'scripts/modul5-snapshot.php')],{env,encoding:'utf8',windowsHide:true}));
const logs=()=>syncfs.readFileSync(path.join(root,'tugas-toko-online/storage/logs/laravel.log'),'utf8').split(/\r?\n/).filter(s=>s.includes('user.role.changed')&&s.includes('M5'));
const target=(db,id)=>db.users.find(u=>u.id_user===id);
const escape=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let browser;
async function shot(page,name){await page.screenshot({path:path.join(shots,name+'.png'),fullPage:true});return name+'.png';}
async function login(username){
    const context=await browser.newContext({viewport:{width:1280,height:900}}),page=await context.newPage();
    await page.goto(base+'/login');await page.locator('#username').fill(username);await page.locator('#password').fill(username==='budi'?'rahasia123':'BelajarRole5!');
    await page.getByRole('button',{name:/Masuk/}).click();await page.waitForURL(base+'/');return {context,page};
}
async function request(actor,method,url,payload,json=true){
    const csrf=await actor.page.locator('input[name="_token"]').first().inputValue();
    const response=await actor.context.request.fetch(base+url,{method,headers:{'X-CSRF-TOKEN':csrf,'Accept':json?'application/json':'text/html','Referer':base+'/admin/users'},data:payload,maxRedirects:0});
    const status=response.status(),body=await response.text();
    return {status,response:response.headers()['content-type']?.includes('application/json')&&body?JSON.parse(body):{location:response.headers().location??null}};
}
async function record(id,name,actor,method,url,payload,expected,idTarget='M5CUSTOMER',json=true){
    const before=snapshot(),old=logs(),response=await request(actor,method,url,payload,json),after=snapshot(),afterLogs=logs();
    assert.equal(response.status,expected);
    const item={id,name,method,url,payload,status:response.status,before:target(before,idTarget),after:target(after,idTarget),logsAdded:afterLogs.length-old.length,log:afterLogs.slice(old.length),response:response.response,screenshot:id+'.png'};
    result.scenarios.push(item);await panel(item);console.log('PASS',id,name,response.status,'logs',item.logsAdded);return item;
}
async function panel(item){
    const page=await browser.newPage({viewport:{width:1200,height:900}});
    const html=`<!doctype html><html lang="id"><meta charset="utf-8"><title>Bukti ${escape(item.id)}</title><style>body{font:20px Georgia,serif;color:#111;margin:45px;line-height:1.5}h1{font-size:28px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #aaa;padding:14px;text-align:left}pre{white-space:pre-wrap;word-break:break-word;font:17px Consolas,monospace;border:1px solid #aaa;padding:20px}small{font-size:16px}</style><h1>${escape(item.id)} ${escape(item.name)}</h1><p>${escape(item.method)} ${escape(base+item.url)}<br><strong>Status HTTP aktual: ${item.status}</strong></p><p>Payload: <code>${escape(JSON.stringify(item.payload))}</code></p><table><tr><th>Target</th><th>Role sebelum</th><th>Role sesudah</th><th>Log baru</th></tr><tr><td>${escape(item.before?.id_user??'-')}</td><td>${escape(item.before?.role??'-')}</td><td>${escape(item.after?.role??'-')}</td><td>${item.logsAdded}</td></tr></table><h2>Respons aman</h2><pre>${escape(JSON.stringify(item.response,null,2))}</pre><h2>Log Observer yang baru</h2><pre>${escape(item.log.join('\n')||'Tidak ada log user.role.changed baru.')}</pre><small>Ringkasan bukti ini dibentuk dari respons HTTP, SELECT database lokal, dan baris log aktual. Password, hash, token CSRF, dan cookie tidak ditampilkan.</small></html>`;
    await fs.writeFile(path.join(dir,item.id+'.html'),html);await page.setContent(html);await shot(page,item.id);await page.close();
}
(async()=>{
    await fs.mkdir(shots,{recursive:true});browser=await chromium.launch(chromeOptions());result.baseline=snapshot();
    assert.equal(target(result.baseline,'M5CUSTOMER').role,'customer','Jalankan pada database demo awal (customer).');
    const admin=await login('admin_modul5'),customer=await login('customer_modul5'),otherCustomer=await login('budi'),staff=await login('staff_modul5');
    await admin.page.goto(base+'/admin/users');await shot(admin.page,'01-role-awal');
    await admin.page.goto(base+'/dashboard');await shot(admin.page,'02-dashboard-admin');await admin.page.goto(base+'/admin/users');
    await customer.page.goto(base+'/stateless');
    for(const name of ['Buku Tulis','Buku Tulis','Pulpen']){
      const response=customer.page.waitForResponse(r=>r.url().endsWith('/cart/add')&&r.request().method()==='POST');
      await customer.page.getByRole('button',{name:new RegExp(name+' —')}).click();const r=await response;assert.equal(r.status(),200);
      await customer.page.waitForFunction(()=>!document.querySelector('button').disabled);
    }
    assert.match(await customer.page.locator('#total').innerText(),/13.000/);await shot(customer.page,'03-cart-customer');
    await customer.page.reload();assert.match(await customer.page.locator('#total').innerText(),/Rp0/);await shot(customer.page,'04-cart-refresh');
    await customer.page.goto(base+'/dashboard');
    const valid=await record('E01','Admin mengubah customer menjadi staff',admin,'PATCH','/admin/users/M5CUSTOMER/role',{role:'staff'},302);
    assert.equal(valid.before.role,'customer');assert.equal(valid.after.role,'staff');assert.equal(valid.logsAdded,1);
    await admin.page.goto(base+'/admin/users');await shot(admin.page,'05-role-berubah');
    const denied=await record('E02','Customer mencoba PATCH role',otherCustomer,'PATCH','/admin/users/M5STAFF/role',{role:'customer'},403,'M5STAFF');assert.equal(denied.logsAdded,0);assert.equal(denied.after.role,'staff');
    await record('E03','Admin mencoba mengubah role dirinya',admin,'PATCH','/admin/users/M5ADMIN/role',{role:'staff'},422,'M5ADMIN');
    await record('E04','Input role admin ditolak validasi',admin,'PATCH','/admin/users/M5STAFF/role',{role:'admin'},422,'M5STAFF');
    const noop=await record('E05','Role target sama sehingga tidak ada save',admin,'PATCH','/admin/users/M5CUSTOMER/role',{role:'staff'},302);assert.equal(noop.logsAdded,0);
    await record('E06','Staff mencoba cart dengan payload valid',staff,'POST','/cart/add',{cart:{1:2},product_id:2},403,'M5STAFF');
    await record('E07','Sesi customer lama langsung mengikuti role staff baru',customer,'POST','/cart/add',{cart:{1:2},product_id:2},403);
    await record('E08','Admin lain juga dilindungi service',admin,'PATCH','/admin/users/M5ADMIN2/role',{role:'staff'},422,'M5ADMIN2');
    const html=await record('E09','Validasi formulir HTML memakai redirect',admin,'PATCH','/admin/users/M5STAFF/role',{role:'admin'},302,'M5STAFF',false);
    await admin.page.goto(base+'/admin/users');assert.match(await admin.page.locator('body').innerText(),/role/i);await shot(admin.page,'06-validasi-form-html');
    await staff.page.goto(base+'/stok');await shot(staff.page,'07-stok-staff');
    const forbidden=await otherCustomer.page.goto(base+'/admin/users');assert.equal(forbidden.status(),403);await shot(otherCustomer.page,'08-customer-403');
    const guest=await browser.newContext();const guestResponse=await guest.request.get(base+'/admin/users',{maxRedirects:0});result.guest={htmlStatus:guestResponse.status(),location:guestResponse.headers().location};assert.equal(result.guest.htmlStatus,302);await guest.close();
    const beforeBulk=snapshot(),l1=logs();execFileSync(php,[path.join(root,'scripts/modul5-observer-experiment.php'),'bulk'],{env,windowsHide:true});
    const afterBulk=snapshot(),l2=logs();assert.equal(target(afterBulk,'M5CUSTOMER').role,'customer');assert.equal(l2.length,l1.length);
    const bulk={id:'F04',name:'Bulk update mengubah DB tanpa event model per baris',method:'CLI lokal',url:'User::whereKey(...)->update(...)',payload:{role:'customer'},status:0,before:target(beforeBulk,'M5CUSTOMER'),after:target(afterBulk,'M5CUSTOMER'),logsAdded:0,log:[],response:{hasil:'Role berubah melalui query bulk; Observer tidak dipanggil.'},screenshot:'F04.png'};await panel(bulk);
    execFileSync(php,[path.join(root,'scripts/modul5-observer-experiment.php'),'restore'],{env,windowsHide:true});const final=snapshot(),l3=logs();assert.equal(target(final,'M5CUSTOMER').role,'staff');assert.equal(l3.length,l2.length+1);
    result.observer={bulk,restore:{role:'staff',logsAdded:1,log:l3.slice(l2.length)}};result.final=final;
    await fs.writeFile(path.join(dir,'role-log.txt'),l3.join('\n')+'\n');
    result.summary={roleTests:17,roleAssertions:100,allTests:44,allAssertions:369,htmlValidationStatus:html.status};
})().catch(e=>{result.error=e.stack;process.exitCode=1;console.error(e);}).finally(async()=>{await browser?.close();await fs.writeFile(path.join(dir,'results.json'),JSON.stringify(result,null,2));});
