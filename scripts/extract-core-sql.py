with open('c:/laragon/www/simpeg/sikap_data_unri.sql', 'r', encoding='utf-8') as f:
    text = f.read()

lines = text.split('\n')
sql_queries = ['SET FOREIGN_KEY_CHECKS=0;']
targets = [
    'REPLACE INTO `roles`',
    'REPLACE INTO `unit_kerja`',
    'REPLACE INTO `jenis_jabatan`',
    'REPLACE INTO `golongan`',
    'REPLACE INTO `jabatan`',
    'REPLACE INTO `pegawai`',
    'REPLACE INTO `users`'
]

for line in lines:
    line_clean = line.strip()
    for t in targets:
        if line_clean.startswith(t):
            sql_queries.append(line_clean)
            break

sql_queries.append('SET FOREIGN_KEY_CHECKS=1;')

with open('c:/laragon/www/simpeg/sikap_data_inti_copy_paste.sql', 'w', encoding='utf-8') as f:
    f.write('\n\n'.join(sql_queries))

print('Selesai! Dihasilkan file dengan', len(sql_queries), 'blok query.')
