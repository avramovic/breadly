##
## :::::::::  :::::::::  ::::::::::     :::     :::::::::  :::     :::   ::: 
## :+:    :+: :+:    :+: :+:          :+: :+:   :+:    :+: :+:     :+:   :+: 
## +:+    +:+ +:+    +:+ +:+         +:+   +:+  +:+    +:+ +:+      +:+ +:+  
## +#++:++#+  +#++:++#:  +#++:++#   +#++:++#++: +#+    +:+ +#+       +#++:   
## +#+    +#+ +#+    +#+ +#+        +#+     +#+ +#+    +#+ +#+        +#+    
## #+#    #+# #+#    #+# #+#        #+#     #+# #+#    #+# #+#        #+#    
## #########  ###    ### ########## ###     ### #########  ########## ### 
##
## Welcome to your own personal Breadly gitpod instance.
##
## To start the development server type the following command in the terminal:  
##
## php artisan serve
##
## The first time you do this it will ask you to make port 8000 public. You should 
## do this in order to make your instance publicly available.
##
## Your public URL will be:
##
## ${GITPOD_WORKSPACE_URL}
##
echo Please wait...
sleep 10
mysql -u root -e "create database breadly"
cp .env.example .env
sed -i "s|APP_URL=http://localhost|APP_URL=${GITPOD_WORKSPACE_URL}|g" .env
sed -i "s|APP_URL=https://|APP_URL=https://8000-|g" .env
composer install
php artisan key:generate
php artisan migrate --seed