README
======

This directory should be used to place project specific documentation including
but not limited to project notes, generated API/phpdoc documentation, or
manual files generated or hand written.  Ideally, this directory would remain
in your development environment only and should not be deployed with your
application to its final production location.


Setting Up Your VHOST
=====================

The following is a sample VHOST you might want to consider for your project.

<VirtualHost *:80>
   DocumentRoot "/mnt/www/mediaspace/social/mediaspace_v3/public"
   ServerName mediaspace_v3.local

   # This should be omitted in the production environment
   SetEnv APPLICATION_ENV development

   <Directory "/mnt/www/mediaspace/social/mediaspace_v3/public">
       Options Indexes MultiViews FollowSymLinks
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>

</VirtualHost>
