import urllib.request
import urllib.parse
import http.cookiejar
import ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(
    urllib.request.HTTPSHandler(context=ctx),
    urllib.request.HTTPCookieProcessor(cj)
)

# 1. Get CSRF token
resp = opener.open('https://sikap.fkp.unri.ac.id/login')
html = resp.read().decode('utf-8')
token_marker = 'name="_token" value="'
token_start = html.find(token_marker) + len(token_marker)
token_end = html.find('"', token_start)
token = html[token_start:token_end]

# 2. Try login with admin
data = urllib.parse.urlencode({
    '_token': token,
    'login': '198006152025211060',
    'password': 'password'
}).encode('utf-8')

req = urllib.request.Request('https://sikap.fkp.unri.ac.id/login', data=data)
try:
    res = opener.open(req)
    body = res.read().decode('utf-8')
    if 'These credentials do not match our records' in body:
        print('STATUS: CREDENTIALS_NOT_FOUND (data belum masuk)')
    elif 'dashboard' in res.geturl():
        print('STATUS: BERHASIL_LOGIN')
    else:
        print('STATUS: RESPONSE_URL =', res.geturl())
except urllib.error.HTTPError as e:
    print('HTTP ERROR:', e.code)
