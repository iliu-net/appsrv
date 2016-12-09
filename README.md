# appsrv

This is my personal application server.

Available Apps:

- [Tiny Tiny RSS](https://tt-rss.org/gitlab/fox/tt-rss/wikis/home)
- time planner - multi time zone meeting planner
- myip - show the visitor's IP
- mushupork - tasty!

# Creating a new app:

    rhc app create $appname php-5.4 mysql-5.5 phpmyadmin-4 cron-1.4 --from-code=$giturl

