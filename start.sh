#!/bin/bash

# Iniciar supervisord en segundo plano
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf &

# Iniciar Apache en primer plano
exec apache2-foreground
