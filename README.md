Aivo Challenge (*)
---

En necesario crear un VH in Apache (httpd-vhosts.conf)

```
<VirtualHost *:80>
    DocumentRoot "D:/AppServ/www/aivo_challenge"
    ServerName local.aivochallenge.com
</VirtualHost>
```

Agregar al archivo host. En Windows se encuentra en "X:\Windows\System32\drivers\etc\hosts"

```
127.0.0.1     local.aivochallenge.com
```

¿Cómo Funciona?
---

`http://local.aivochallenge.com/api/v1/albums?q=[Nombre de la Banda]`

Retorna un Array de toda la discografía

```
[{
    "name": "Album Name",
    "released": "10-10-2010",
     "tracks": 10,
     "cover": {
         "height": 640,
         "width": 640,
         "url": "https://i.scdn.co/image/6c951f3f334e05ffa"
     }
 },
  ...
]
```

(*) Requiere tener instalado composer.