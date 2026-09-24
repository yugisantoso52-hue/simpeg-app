import urllib.request, urllib.parse, http.cookiejar, ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE
cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPSHandler(context=ctx), urllib.request.HTTPCookieProcessor(cj))

# 1. Login
resp = opener.open('https://sikap.fkp.unri.ac.id/login')
html = resp.read().decode('utf-8')
marker = 'name="_token" value="'
t_start = html.find(marker) + len(marker)
t_end = html.find('"', t_start)
token = html[t_start:t_end]

data = urllib.parse.urlencode({'_token': token, 'login': 'admin@simpeg.test', 'password': 'admin12345'}).encode('utf-8')
opener.open(urllib.request.Request('https://sikap.fkp.unri.ac.id/login', data=data))

# 2. Check 10 requests
results = []
for i in range(10):
    res = opener.open('https://sikap.fkp.unri.ac.id/dashboard')
    content = res.read().decode('utf-8')
    is_new = 'paginatedItems' in content
    results.append(is_new)

print('10 Requests Results (True=New, False=Old):', results)
print('Count True (New):', results.count(True))
print('Count False (Old):', results.count(False))
