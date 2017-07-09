<p align="center">
    <img src="https://raw.githubusercontent.com/ZeroSide-Project/zeroside/master/assets/images/favicon/android-icon-192x192.png" alt="icon"/>
    <h1 align="center">ZeroSide</h1>
    <h3 align="center">Anonymous File-Sharing</h3>
</p>

## How it works ?

> ZeroSide is a free and open source project that allow users to share their files anonymously. Indeed, there is no saving of sensitives informations (Geolocation, IP, etc...)

## Dependencies

> This project is runned by :
- Bulma.io, this is the CSS framework that we use
- AltoRouter, this is a free URL router that we use to simplify the code
- Pug (formelly Jade), a easier HTML

## Contributors

> Thanks to :
- [LXT](https://github.com/l-x-0x7) : Chef developer
- [EmpireIsHere](https://github.com/empireishere) : Tester and API contributor
- [Zeq](https://github.com/Kqdi) : Tester and API contributor

## Tips for setup

1. Blank page after clone/download repo
    - This is often due to database, go put your database login in the begin of `index.php`
    - To setup fastly the database, use MySQL and import the `setup.sql` at root of the repository

2. Unblock the 2M or 8M limit
    - Modify your `php.ini` and find :
        - `post_max_size`, replace value by `10000M` (**do not use `10G`**)

        - `upload_max_filesize`, replace value by `10000M` too

    - If you are using `nginx`, put this in your `nginx.conf` :
        ```
        http {
        ...
            client_max_body_size 10000M;
        ...
        }```
3. Increase security and stability :
    - Use a SSL certificate (`Let's Encrypt` is good enough)
    - Use HTTP/2 protocol for better compression (Included in lasts NGINX versions)

## Contribute

> You can contribute to the code, just fork this repo and make a pull-request !

> Else, you can also donate to us :
- [PayPal.me](https://www.paypal.me/syscco)
- Bitcoin : **173yvc32M4bETCZxP8z6AGs1Zq9C6rB3CP**

> Thanks you so much if you contribute!
