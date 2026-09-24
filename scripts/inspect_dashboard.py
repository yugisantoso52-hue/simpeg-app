import urllib.request, urllib.parse, http.cookiejar, ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE
cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPSHandler(context=ctx), urllib.request.HTTPCookieProcessor(cj))

# 1. Login as admin
resp = opener.open('https://sikap.fkp.unri.ac.id/login')
html = resp.read().decode('utf-8')
marker = 'name="_token" value="'
t_start = html.find(marker) + len(marker)
t_end = html.find('"', t_start)
token = html[t_start:t_end]

data = urllib.parse.urlencode({
    '_token': token,
    'login': 'admin@simpeg.test',
    'password': 'admin12345'
}).encode('utf-8')

req = urllib.request.Request('https://sikap.fkp.unri.ac.id/login', data=data)
res = opener.open(req)

# 2. Get dashboard HTML
dash_res = opener.open('https://sikap.fkp.unri.ac.id/dashboard')
dash_html = dash_res.read().decode('utf-8')

print("Status code:", dash_res.getcode())
print("Dashboard URL:", dash_res.geturl())

checks = [
    "paginatedItems",
    "max-h-[380px]",
    "Sembunyikan Tabel",
    "Semua Pegawai",
    "Monitoring Kelengkapan Data Pegawai Fakultas"
]

for c in checks:
    print(f"Check '{c}':", c in dash_html)

with open('c:/laragon/www/simpeg/scripts/server_dashboard.html', 'w', encoding='utf-8') as f:
    f.write(dash_html)
print("Saved server dashboard HTML to scripts/server_dashboard.html")
