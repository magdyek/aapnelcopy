<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>aaPanel Smart Migrator</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .step-active { background-color: #3b82f6; color: white; }
        .step-done { background-color: #10b981; color: white; }
        .step-pending { background-color: #334155; color: #94a3b8; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen p-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-emerald-400">
                    aaPanel Smart Migrator
                </h1>
                <p class="text-slate-400 mt-2">الأداة الذكية لنقل المواقع وقواعد البيانات بين سيرفرات aaPanel بسهولة</p>
            </div>
            <div class="hidden sm:block">
                <i class="ph ph-rocket-launch text-6xl text-blue-500"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- FORM SECTION -->
            <div class="lg:col-span-2 space-y-6">
                <form id="migrationForm" class="space-y-6">
                    
                    <!-- New Server (AAPanel) -->
                    <div class="glass p-6 rounded-2xl shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <i class="ph-fill ph-server text-emerald-400 text-2xl"></i>
                                <h2 class="text-xl font-semibold">1. السيرفر الجديد (aaPanel الحالي)</h2>
                            </div>
                            <button type="button" id="testNewBtn" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-600 rounded-lg text-sm flex items-center gap-2 transition-colors">
                                <i class="ph ph-plugs"></i>
                                فحص الاتصال
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-slate-400 mb-1">aaPanel URL</label>
                                <input type="text" name="aapanel_url" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 transition-colors" placeholder="http://192.168.1.1:8888" required>
                            </div>
                            <div>
                                <label class="block text-sm text-slate-400 mb-1" title="من لوحة التحكم: Settings -> API">API secret key</label>
                                <input type="password" name="aapanel_key" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 transition-colors" placeholder="API secret key (e.g. j1D9...)" required>
                            </div>
                        </div>
                    </div>

                    <!-- Old Server (aaPanel API) -->
                    <div class="glass p-6 rounded-2xl shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <i class="ph-fill ph-hard-drives text-rose-400 text-2xl"></i>
                                <h2 class="text-xl font-semibold">2. السيرفر القديم (عبر aaPanel API)</h2>
                            </div>
                            <button type="button" id="testOldBtn" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-600 rounded-lg text-sm flex items-center gap-2 transition-colors">
                                <i class="ph ph-plugs"></i>
                                فحص الاتصال
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-slate-400 mb-1">Old aaPanel URL</label>
                                <input type="text" name="old_aapanel_url" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 transition-colors" placeholder="http://192.168.1.5:8888" required>
                            </div>
                            <div>
                                <label class="block text-sm text-slate-400 mb-1" title="من لوحة التحكم: Settings -> API">Old API secret key</label>
                                <input type="password" name="old_aapanel_key" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 transition-colors" placeholder="API secret key (e.g. x8F...)" required>
                            </div>
                        </div>

                        <!-- What to migrate? -->
                        <div class="mt-4 p-4 border border-blue-500/30 bg-blue-900/10 rounded-lg">
                            <label class="block text-sm text-blue-300 font-bold mb-3">ماذا تريد أن تنقل؟</label>
                            <div class="flex items-center gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="migrate_type" value="both" checked class="w-4 h-4 text-blue-500">
                                    <span>الموقع + قاعدة البيانات</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="migrate_type" value="files" class="w-4 h-4 text-blue-500">
                                    <span>الموقع (الملفات الحالية) فقط</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="migrate_type" value="db" class="w-4 h-4 text-blue-500">
                                    <span>قاعدة البيانات فقط</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Dynamic Selection Area (Hidden initially) -->
                        <div id="old_selection_area" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                            <div id="old_site_container">
                                <label class="block text-sm text-amber-400 mb-1 font-bold">الموقع المُراد نقله</label>
                                <select id="old_site_id" name="old_site_id" class="w-full bg-slate-800 border border-amber-500/50 rounded-lg px-4 py-2 focus:outline-none focus:border-amber-500 transition-colors">
                                    <option value="">-- فحص الاتصال أولاً --</option>
                                </select>
                            </div>
                            <div id="old_db_container">
                                <label class="block text-sm text-amber-400 mb-1 font-bold">قاعدة البيانات المُراد نقلها</label>
                                <select id="old_db_id" name="old_db_id" class="w-full bg-slate-800 border border-amber-500/50 rounded-lg px-4 py-2 focus:outline-none focus:border-amber-500 transition-colors">
                                    <option value="">-- فحص الاتصال أولاً --</option>
                                </select>
                            </div>
                            
                            <!-- DB Backups List (Loaded dynamically when DB is selected) -->
                            <div id="old_db_backup_container" class="col-span-1 md:col-span-2 hidden p-4 border border-indigo-500/30 bg-indigo-900/10 rounded-lg">
                                <label class="block text-sm text-indigo-300 font-bold mb-3">النسخ الاحتياطية المتوفرة للقاعدة</label>
                                <div class="flex items-center gap-2">
                                    <select id="old_db_backup_id" name="old_db_backup_id" class="flex-1 bg-slate-800 border border-indigo-500/50 rounded-lg px-4 py-2 focus:outline-none focus:border-indigo-500 transition-colors">
                                        <option value="NEW">-- 🆕 إنشاء نسخة احتياطية جديدة الآن --</option>
                                    </select>
                                    <button type="button" id="deleteBackupBtn" class="hidden px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center gap-1" title="حذف النسخة المحددة من السيرفر القديم لتوفير المساحة">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-indigo-200 mt-2 opacity-80"><i class="ph ph-info"></i> يمكنك اختيار نسخة سابقة لنقلها مباشرة لتوفير الوقت، أو اختيار إنشاء نسخة حديثة الآن.</p>
                            </div>
                        </div>

                        <hr class="border-slate-700 my-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                           <div class="col-span-2">
                                <p class="text-sm text-blue-300">
                                    <i class="ph ph-info"></i> ميزة ذكية: الأداة ستتصل بسيرفرك القديم تلقائياً عبر הـ API لجلب المواقع والقواعد وعمل Backups بضغطة زر دون الحاجة لبيانات الـ Root!
                                </p>
                           </div>
                        </div>
                    </div>

                    <!-- App Details -->
                    <div class="glass p-6 rounded-2xl shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <i class="ph-fill ph-globe text-blue-400 text-2xl"></i>
                                <h2 class="text-xl font-semibold">3. إعدادات السيرفر الجديد</h2>
                            </div>
                            <span class="text-xs text-blue-300 bg-blue-900/40 px-3 py-1 rounded-full border border-blue-500/30">
                                💡 ستقوم الأداة بإنشاء المطلوب أوتوماتيكياً
                            </span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div id="new_domain_wrap">
                                <label id="new_domain_label" class="block text-sm text-slate-400 mb-1">اسم الدومين الجديد (لإنشائه)</label>
                                <input type="text" id="new_domain" name="domain" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 transition-colors" placeholder="domain.com" required>
                                <p class="text-xs text-slate-500 mt-1">المسار سيكون: /www/wwwroot/domain</p>
                            </div>
                            <div id="new_db_name_wrap">
                                <label class="block text-sm text-slate-400 mb-1">اسم القاعدة الجديدة (لإنشائها)</label>
                                <input type="text" id="new_db_name" name="new_db_name" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 transition-colors" placeholder="newdb" required>
                                <p class="text-xs text-slate-500 mt-1">سنقوم بإنشائها ورفع الباك آب بداخلها</p>
                            </div>
                            <div id="new_db_pass_wrap">
                                <label class="block text-sm text-slate-400 mb-1">باسوورد القاعدة الجديدة</label>
                                <input type="text" id="new_db_pass" name="new_db_pass" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 transition-colors" placeholder="****" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="startBtn" class="w-full py-4 bg-gradient-to-r from-blue-600 to-emerald-600 hover:from-blue-500 hover:to-emerald-500 rounded-xl font-bold text-lg shadow-lg transform transition active:scale-95 flex items-center justify-center gap-2">
                        <i class="ph ph-play-circle text-2xl"></i>
                        بدء النقل الذكي
                    </button>
                </form>
            </div>

            <!-- SIDEBAR: PROGRESS -->
            <div class="glass p-6 rounded-2xl shadow-xl h-fit">
                <h2 class="text-xl font-semibold mb-6 flex items-center gap-2">
                    <i class="ph ph-chart-line-up text-blue-400"></i>
                    حالة النقل (Progress)
                </h2>

                <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-600 before:to-transparent">
                    
                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active" id="step-0">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-slate-700 bg-slate-800 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 step-indicator transition-colors">
                            <i class="ph-bold ph-plugs"></i>
                        </div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] glass p-4 rounded-xl shadow-lg ml-0 md:ml-4 rtl:mr-0 rtl:md:mr-4 rtl:md:ml-0">
                            <h3 class="font-bold text-slate-200">فحص الاتصال</h3>
                            <p class="text-xs text-slate-400 mt-1 step-status">في الانتظار...</p>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group" id="step-1">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-slate-700 bg-slate-800 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 step-indicator transition-colors">
                            <i class="ph-bold ph-plus-circle"></i>
                        </div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] glass p-4 rounded-xl shadow-lg ml-0 md:ml-4 rtl:mr-0 rtl:md:mr-4 rtl:md:ml-0">
                            <h3 class="font-bold text-slate-200">إنشاء الموقع</h3>
                            <p class="text-xs text-slate-400 mt-1 step-status">في الانتظار...</p>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group" id="step-2">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-slate-700 bg-slate-800 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 step-indicator transition-colors">
                            <i class="ph-bold ph-cube"></i>
                        </div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] glass p-4 rounded-xl shadow-lg ml-0 md:ml-4 rtl:mr-0 rtl:md:mr-4 rtl:md:ml-0">
                            <h3 class="font-bold text-slate-200">تجهيز الباك آب</h3>
                            <p class="text-xs text-slate-400 mt-1 step-status">في الانتظار...</p>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group" id="step-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-slate-700 bg-slate-800 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 step-indicator transition-colors">
                            <i class="ph-bold ph-download-simple"></i>
                        </div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] glass p-4 rounded-xl shadow-lg ml-0 md:ml-4 rtl:mr-0 rtl:md:mr-4 rtl:md:ml-0">
                            <h3 class="font-bold text-slate-200">سحب البيانات</h3>
                            <p class="text-xs text-slate-400 mt-1 step-status">في الانتظار...</p>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group" id="step-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-slate-700 bg-slate-800 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 step-indicator transition-colors">
                            <i class="ph-bold ph-database"></i>
                        </div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] glass p-4 rounded-xl shadow-lg ml-0 md:ml-4 rtl:mr-0 rtl:md:mr-4 rtl:md:ml-0">
                            <h3 class="font-bold text-slate-200">استعادة وإعداد</h3>
                            <p class="text-xs text-slate-400 mt-1 step-status">في الانتظار...</p>
                        </div>
                    </div>

                </div>

                <div id="errorBox" class="mt-6 p-4 bg-red-900/50 border border-red-500 rounded-lg text-sm text-red-200 hidden">
                    <i class="ph ph-warning-circle inline text-lg mr-1"></i>
                    <span id="errorMsg">حدث خطأ</span>
                </div>

                <div id="successBox" class="mt-6 p-4 bg-emerald-900/50 border border-emerald-500 rounded-lg text-sm text-emerald-200 hidden">
                    <i class="ph ph-check-circle inline text-lg mr-1"></i>
                    تم نقل الموقع وقاعدة البيانات بنجاح!
                </div>
            </div>

        </div>
    </div>

    <script>
        const form = document.getElementById('migrationForm');
        const startBtn = document.getElementById('startBtn');
        const errorBox = document.getElementById('errorBox');
        const errorMsg = document.getElementById('errorMsg');
        const successBox = document.getElementById('successBox');
        
        // Data variables
        // Data variables
        let remoteZip = '';
        let remoteSql = '';
        let formData = new FormData();
        
        // Global lists for later
        let testResOld = null;

        // Handle Radio changes
        const radios = document.querySelectorAll('input[name="migrate_type"]');
        radios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                const type = e.target.value;
                const cSite = document.getElementById('old_site_container');
                const cDb = document.getElementById('old_db_container');
                
                const newDom = document.getElementById('new_domain');
                const newDbName = document.getElementById('new_db_name');
                const newDbPass = document.getElementById('new_db_pass');
                
                const newDomW = document.getElementById('new_domain_wrap');
                const newDbNameW = document.getElementById('new_db_name_wrap');
                const newDbPassW = document.getElementById('new_db_pass_wrap');

                if (type === 'both') {
                    cSite.classList.remove('hidden');
                    cDb.classList.remove('hidden');
                    newDomW.classList.remove('hidden');
                    newDbNameW.classList.remove('hidden');
                    newDbPassW.classList.remove('hidden');
                    
                    document.getElementById('new_domain_label').innerText = "اسم الدومين الجديد (لإنشائه)";
                    
                    newDom.required = true;
                    newDbName.required = true;
                    newDbPass.required = true;
                } else if (type === 'files') {
                    cSite.classList.remove('hidden');
                    cDb.classList.add('hidden');
                    newDomW.classList.remove('hidden');
                    newDbNameW.classList.add('hidden');
                    newDbPassW.classList.add('hidden');
                    
                    document.getElementById('new_domain_label').innerText = "اسم الدومين الحالي (لتحديث ملفاته)";
                    
                    newDom.required = true;
                    newDbName.required = false;
                    newDbPass.required = false;
                } else if (type === 'db') {
                    cSite.classList.add('hidden');
                    cDb.classList.remove('hidden');
                    newDomW.classList.add('hidden');
                    newDbNameW.classList.remove('hidden');
                    newDbPassW.classList.remove('hidden');
                    
                    newDom.required = false; 
                    newDbName.required = true;
                    newDbPass.required = true;
                }
            });
        });
        
        // Test New Server Connection
        document.getElementById('testNewBtn').addEventListener('click', async () => {
            const btn = document.getElementById('testNewBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> جاري الفحص...';
            formData = new FormData(form);
            try {
                formData.set('action', 'test_new_connection');
                const response = await fetch('ajax.php', { method: 'POST', body: formData });
                const res = await response.json();
                if(response.ok && res.status === 'success') {
                    btn.className = "px-4 py-2 bg-emerald-600/20 text-emerald-400 border border-emerald-500/50 rounded-lg text-sm flex items-center gap-2 transition-colors";
                    btn.innerHTML = '<i class="ph ph-check-circle"></i> متصل';
                } else {
                    throw new Error(res.message);
                }
            } catch (e) {
                btn.className = "px-4 py-2 bg-red-600/20 text-red-400 border border-red-500/50 rounded-lg text-sm flex items-center gap-2 transition-colors";
                btn.innerHTML = '<i class="ph ph-warning-circle"></i> فشل الاتصال';
                alert(e.message);
            } finally {
                btn.disabled = false;
            }
        });

        // Test Old Server Connection
        document.getElementById('testOldBtn').addEventListener('click', async () => {
            const btn = document.getElementById('testOldBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> جاري الفحص...';
            formData = new FormData(form);
            try {
                formData.set('action', 'test_old_connection');
                const response = await fetch('ajax.php', { method: 'POST', body: formData });
                const textOutput = await response.text();
                let res;
                try {
                     res = JSON.parse(textOutput);
                } catch(jErr) {
                     console.error("RAW:", textOutput);
                     throw new Error("استجابة غير صالحة من السيرفر. ربما الـ API معطل.");
                }
                
                if(response.ok && res.status === 'success') {
                    btn.className = "px-4 py-2 bg-emerald-600/20 text-emerald-400 border border-emerald-500/50 rounded-lg text-sm flex items-center gap-2 transition-colors";
                    btn.innerHTML = '<i class="ph ph-check-circle"></i> متصل وتم جلب البيانات';
                    testResOld = res; // Save for later steps
                    
                    document.getElementById('old_selection_area').classList.remove('hidden');

                    // Populate Dropdowns safely handling aaPanel API array structures
                    const siteSelect = document.getElementById('old_site_id');
                    const dbSelect = document.getElementById('old_db_id');
                    
                    siteSelect.innerHTML = '<option value="">-- اختر الموقع --</option>';
                    // Some aaPanel API versions return objects instead of arrays or double wrap data
                    let sitesList = Array.isArray(res.old_sites) ? res.old_sites : [];
                    let dbsList = Array.isArray(res.old_dbs) ? res.old_dbs : [];

                    if(sitesList.length > 0) {
                        sitesList.forEach(site => {
                            if(site.id && site.name) {
                                siteSelect.innerHTML += `<option value="${site.id}" data-name="${site.name}" data-path="${site.path}">${site.name} (${site.path})</option>`;
                            }
                        });
                    } else {
                        siteSelect.innerHTML += '<option disabled>لا يوجد مواقع</option>';
                    }

                    dbSelect.innerHTML = '<option value="">-- اختر قاعدة البيانات --</option>';
                    if(dbsList.length > 0) {
                        dbsList.forEach(db => {
                            if(db.id && db.name) {
                                dbSelect.innerHTML += `<option value="${db.id}" data-name="${db.name}" data-user="${db.username}">${db.name}</option>`;
                            }
                        });
                    } else {
                        dbSelect.innerHTML += '<option disabled>لا يوجد داتابيز</option>';
                    }
                    
                } else {
                    throw new Error(res.message);
                }
            } catch (e) {
                btn.className = "px-4 py-2 bg-red-600/20 text-red-400 border border-red-500/50 rounded-lg text-sm flex items-center gap-2 transition-colors";
                btn.innerHTML = '<i class="ph ph-warning-circle"></i> فشل الاتصال';
                alert(e.message);
            } finally {
                btn.disabled = false;
            }
        });
        
        // Fetch DB Backups when a DB is selected
        document.getElementById('old_db_id').addEventListener('change', async (e) => {
            const dbId = e.target.value;
            const backupContainer = document.getElementById('old_db_backup_container');
            const backupSelect = document.getElementById('old_db_backup_id');
            const deleteBtn = document.getElementById('deleteBackupBtn');
            
            backupContainer.classList.add('hidden');
            backupSelect.innerHTML = '<option value="NEW">-- 🆕 إنشاء نسخة احتياطية جديدة الآن --</option>';
            deleteBtn.classList.add('hidden');
            
            if (!dbId) return;
            
            backupContainer.classList.remove('hidden');
            backupSelect.options[0].text = "جاري البحث عن نسخ متوفرة...";
            
             try {
                formData = new FormData(form);
                formData.set('action', 'list_db_backups');
                const response = await fetch('ajax.php', { method: 'POST', body: formData });
                const res = await response.json();
                
                backupSelect.innerHTML = '<option value="NEW">-- 🆕 إنشاء نسخة احتياطية جديدة الآن --</option>';
                
                if(response.ok && res.status === 'success' && res.backups.length > 0) {
                    res.backups.forEach(backup => {
                        // value is the filename, we store backup ID in data-id for deletion
                        const option = document.createElement('option');
                        option.value = backup.filename;
                        option.dataset.id = backup.id;
                        
                        // Formulate a nice size string
                        const sizeMB = (parseInt(backup.size) / (1024*1024)).toFixed(2);
                        option.text = `📦 ${backup.name} (${sizeMB} MB) - ${backup.addtime}`;
                        
                        backupSelect.appendChild(option);
                    });
                } else if(!response.ok) {
                     console.error("Backups Check Failed:", res.message);
                }
            } catch (err) {
                 console.error("Error fetching backups:", err);
            } finally {
                if(backupSelect.options.length === 1) {
                     backupSelect.options[0].text = "-- 🆕 لم يتم العثور على نسخ سابقة، سيتم إنشاء نسخة جديدة --";
                }
            }
        });
        
        // Handle Backup Selection Change to show/hide Delete Button
        document.getElementById('old_db_backup_id').addEventListener('change', (e) => {
             const val = e.target.value;
             const deleteBtn = document.getElementById('deleteBackupBtn');
             if(val !== 'NEW') {
                  deleteBtn.classList.remove('hidden');
             } else {
                  deleteBtn.classList.add('hidden');
             }
        });
        
        // Handle Delete Backup
        document.getElementById('deleteBackupBtn').addEventListener('click', async () => {
             const backupSelect = document.getElementById('old_db_backup_id');
             const selectedOption = backupSelect.options[backupSelect.selectedIndex];
             const backupId = selectedOption.dataset.id;
             
             if(!backupId) return;
             if(!confirm("هل أنت متأكد من حذف هذه النسخة الاحتياطية نهائياً من السيرفر القديم؟")) return;
             
             const btn = document.getElementById('deleteBackupBtn');
             btn.disabled = true;
             btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i>';
             
             try {
                formData = new FormData(form);
                formData.set('action', 'delete_db_backup');
                formData.set('backup_id', backupId);
                
                const response = await fetch('ajax.php', { method: 'POST', body: formData });
                const res = await response.json();
                
                if(response.ok && res.status === 'success') {
                     alert("تم الحذف بنجاح!");
                     // Re-trigger change to refresh list
                     document.getElementById('old_db_id').dispatchEvent(new Event('change'));
                } else {
                     throw new Error(res.message);
                }
             } catch(err) {
                  alert(err.message);
             } finally {
                  btn.disabled = false;
                  btn.innerHTML = '<i class="ph ph-trash"></i>';
             }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            errorBox.classList.add('hidden');
            successBox.classList.add('hidden');
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="ph ph-spinner animate-spin text-2xl"></i> جارِ التجهيز...';
            
            formData = new FormData(form);
                
                try {
                    // Make sure we have old server data (force test if not done)
                    if (!testResOld) {
                         throw new Error("يرجى فحص اتصال السيرفر القديم أولاً واختيار الموقع والقاعدة.");
                    } else {
                         updateStepStatus(0, 'done', 'تم الفحص مسبقاً');
                    }
                    
                    const type = document.querySelector('input[name="migrate_type"]:checked').value;
                    
                    const oldSiteId = document.getElementById('old_site_id').value;
                    const oldDbId = document.getElementById('old_db_id').value;

                    if (type === 'both' && (!oldSiteId || !oldDbId)) {
                         throw new Error("يرجى اختيار الموقع وقاعدة البيانات من القوائم للنسخ الكامل.");
                    }
                    if (type === 'files' && !oldSiteId) {
                         throw new Error("يرجى اختيار الموقع من القائمة.");
                    }
                    if (type === 'db' && !oldDbId) {
                         throw new Error("يرجى اختيار قاعدة البيانات من القائمة.");
                    }

                    formData.append('migrate_type', type);
                    formData.append('old_site_id', oldSiteId);
                    formData.append('old_db_id', oldDbId);

                    // Step 1: Create Site and DB via aaPanel
                    await runStep(1, 'create_site_db', 'جاري إنشاء العناصر على السيرفر الجديد...');

                // Step 2: Backup remote
                // If user selected an existing backup, we inject its filename, bypassing DB backup creation
                const selectedDbBackup = document.getElementById('old_db_backup_id') && document.getElementById('old_db_backup_id').value;
                if(selectedDbBackup && selectedDbBackup !== 'NEW' && type !== 'files') {
                     formData.append('existing_db_backup', selectedDbBackup);
                }
                
                const res2 = await runStep(2, 'backup_remote', 'جاري التواصل مع API القديم للتجهيز...');
                formData.append('remote_zip', res2.zip_file || '');
                formData.append('remote_sql', res2.sql_file || '');
                
                // Step 3: Download Backup
                const res3 = await runStep(3, 'download_backup', 'جاري تحميل الباك آب...');
                if (res3.local_sql_file) {
                     formData.append('local_sql_file', res3.local_sql_file);
                }

                // Step 4: Restore and Update Config
                await runStep(4, 'restore_local', 'جاري فك الاستعادة...');
                // Run config update instantly afterwards (if migrating files + db)
                if(type === 'both' || type === 'files') {
                     await performAction('update_config');
                }
                updateStepStatus(4, 'done', 'اكتمل بنجاح.');

                successBox.classList.remove('hidden');

            } catch (err) {
                errorMsg.innerText = err.message;
                errorBox.classList.remove('hidden');
            } finally {
                startBtn.disabled = false;
                startBtn.innerHTML = '<i class="ph ph-play-circle text-2xl"></i> بدء النقل الذكي';
            }
        });

        async function runStep(stepIndex, actionName, loadingMessage) {
            updateStepStatus(stepIndex, 'active', loadingMessage);
            try {
                const res = await performAction(actionName);
                if (res.status === 'success') {
                    updateStepStatus(stepIndex, 'done', res.message || 'اكتملت الخطوة');
                    return res;
                } else {
                    throw new Error(res.message || 'حدث خطأ غير معروف');
                }
            } catch (error) {
                updateStepStatus(stepIndex, 'error', error.message);
                throw error;
            }
        }

        async function performAction(actionName) {
            formData.set('action', actionName);
            const response = await fetch('ajax.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const text = await response.text();
                try {
                    const j = JSON.parse(text);
                    throw new Error(j.message);
                } catch(e) {
                    if (e.message.indexOf('HTTP') === -1) {
                         throw new Error("HTTP " + response.status + ": " + text.substring(0, 500));
                    }
                    throw e;
                }
            }
            return response.json();
        }

        function updateStepStatus(stepIndex, status, msg) {
            const stepEl = document.getElementById(`step-${stepIndex}`);
            if(!stepEl) return;

            const iconDiv = stepEl.querySelector('.step-indicator');
            const statusTxt = stepEl.querySelector('.step-status');

            iconDiv.className = 'flex items-center justify-center w-10 h-10 rounded-full shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 step-indicator transition-colors';

            if (status === 'active') {
                iconDiv.classList.add('bg-blue-500', 'text-white', 'border-blue-400');
                iconDiv.innerHTML = '<i class="ph ph-spinner animate-spin"></i>';
                statusTxt.innerText = msg;
                statusTxt.classList.replace('text-slate-400', 'text-blue-300');
            } else if (status === 'done') {
                iconDiv.classList.add('bg-emerald-500', 'text-white', 'border-emerald-400');
                iconDiv.innerHTML = '<i class="ph-bold ph-check"></i>';
                statusTxt.innerText = msg;
                statusTxt.classList.replace('text-blue-300', 'text-emerald-400');
                statusTxt.classList.replace('text-slate-400', 'text-emerald-400');
            } else if (status === 'error') {
                iconDiv.classList.add('bg-red-500', 'text-white', 'border-red-400');
                iconDiv.innerHTML = '<i class="ph-bold ph-x"></i>';
                statusTxt.innerText = msg;
                statusTxt.classList.replace('text-blue-300', 'text-red-400');
                statusTxt.classList.replace('text-slate-400', 'text-red-400');
            }
        }
    </script>
</body>
</html>
