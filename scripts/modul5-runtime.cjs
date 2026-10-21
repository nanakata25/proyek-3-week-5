// Dedicated local SQLite database; existing MySQL data and .env remain untouched.
const fs=require('node:fs'),path=require('node:path'),{spawnSync,spawn}=require('node:child_process');
const root=path.resolve(__dirname,'..'),app=path.join(root,'tugas-toko-online'),php=path.join(root,'.runtime/php/php.exe');
const database=path.join(root,'.runtime/modul5-demo.sqlite');
const env={...process.env,APP_ENV:'local',APP_DEBUG:'false',APP_URL:'http://127.0.0.1:8015',DB_CONNECTION:'sqlite',DB_DATABASE:database,DB_URL:'',SESSION_DRIVER:'file',SESSION_COOKIE:'modul5_demo_session',LOG_CHANNEL:'single',LOG_LEVEL:'info'};
function artisan(args){const r=spawnSync(php,['artisan',...args],{cwd:app,env,encoding:'utf8',windowsHide:true});if(r.status!==0)throw new Error(r.stderr+r.stdout);return r.stdout;}
if(require.main===module){
  if(!fs.existsSync(database))fs.writeFileSync(database,'');
  for(const args of [['config:clear'],['migrate','--no-interaction'],['db:seed','--no-interaction'],['db:seed','--class=RoleDemoSeeder','--no-interaction']])console.log(artisan(args));
  const out=fs.openSync(path.join(root,'.runtime/modul5-server-out.log'),'a'),err=fs.openSync(path.join(root,'.runtime/modul5-server-error.log'),'a');
  const child=spawn(php,['-S','127.0.0.1:8015','-t','public'],{cwd:app,env,windowsHide:true,detached:true,stdio:['ignore',out,err]});child.unref();
  console.log('Modul 5: http://127.0.0.1:8015 | PID '+child.pid);
}
module.exports={root,app,php,database,env,artisan};
