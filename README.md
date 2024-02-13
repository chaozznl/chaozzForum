# chaozzForum
A lightweight, simple-to-use, responsive, customizable, flatfile (no MySQL) forum. Written as a proof-of-concept for [chaozzDB](https://github.com/chaozznl/chaozzDB).

# Main features:
- Responsive design (mobile friendly!) using `spectre.css`
- Uses `font awesome` for all icons and bbc
- Simple to use for anyone who has ever used forum software
- Appearance can be changed in a single css file
- Uses a `flatfile database engine` (no MySQL needed)
- `SEO friendly` URL’s

# How to start using chaozzForum:
- Upload all the files to a webserver
- chmod the `db` folder to 777 and all `tsv` files in it to 666
- Make sure the `.htaccess` file is present in the `db` folder if you’re on an apache server. If you’re on a Windows server, make sure the tvs files are not directly accessible by the browser
- Login to your forum as admin
- username: `admin`
- password: `fubar`
- Change the password
