"""Layer a downloaded Laravel skeleton, preserving all task-specific source files."""
from pathlib import Path
import shutil, json
root=Path(__file__).resolve().parents[1]
base=root/'.runtime/laravel-base'
apps=[('tugas-1-login','db_tugas',8001,'tugas1_session'),('tugas-2-keranjang','db_keranjang',8002,'tugas2_session'),('tugas-toko-online','db_toko_online',8003,'toko_session')]
skip_dirs={'.git','.github','node_modules','vendor'}
skip_files={'.env','README.md','AGENTS.md','CLAUDE.md','database.sqlite','welcome.blade.php','ExampleTest.php','UserFactory.php','package-lock.json','package.json','vite.config.js','app.css','app.js','bootstrap.js'}
for name,db,port,cookie in apps:
    dest=root/name
    for src in base.rglob('*'):
        rel=src.relative_to(base)
        if any(x in skip_dirs for x in rel.parts) or src.name in skip_files: continue
        dst=dest/rel
        if src.is_dir(): dst.mkdir(parents=True,exist_ok=True)
        elif not dst.exists(): shutil.copy2(src,dst)
    shutil.copytree(base/'vendor',dest/'vendor',dirs_exist_ok=True)
    env=f'''APP_NAME="Modul 4 - {name}"
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=http://127.0.0.1:{port}
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID
LOG_CHANNEL=stack
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE={db}
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_COOKIE={cookie}
SESSION_EXPIRE_ON_CLOSE=false
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
CACHE_STORE=file
QUEUE_CONNECTION=sync
'''
    (dest/'.env').write_text(env,encoding='utf-8')
    (dest/'.env.example').write_text(env.replace('DB_PORT=3307','DB_PORT=3306'),encoding='utf-8')
    config=dest/'config/app.php'
    config.write_text(config.read_text().replace("'timezone' => 'UTC'","'timezone' => 'Asia/Jakarta'"))
    composer=json.loads((dest/'composer.json').read_text())
    composer['scripts']['setup']=['composer install','@php -r "file_exists(\'.env\') || copy(\'.env.example\', \'.env\');"','@php artisan key:generate','@php artisan migrate --seed']
    (dest/'composer.json').write_text(json.dumps(composer,indent=4)+'\n')
    print('Prepared',name)
