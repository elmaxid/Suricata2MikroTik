#! /bin/bash

### BEGIN INIT INFO
# Provides:          suricata2mikrotik
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $local_fs
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Suricata service
# Description:       Run Suricata service sudo -u Suricata-user 
### END INIT INFO

# Carry out specific functions when asked to by the system
case "$1" in
  start)
    echo "Starting Suricata2MikroTik..."
   # bash -c 'cd /var/www/html/suricata2ips/bin/ && ./start-Suricata.sh'
    bash -c 'cd /usr/local/bin/ && start_ips'
    ;;
  stop)
    echo "Stopping Suricata2MikroTik..."
     # bash -c 'cd /var/www/html/suricata2ips/bin/ && ./stop-Suricata.sh'
     bash -c 'rm /tmp/suricata2mikrotik.pid '
    sleep 2
    ;;
    restart)
        $0 stop
        sleep 2
        $0 start
    ;;
    
  *)
    echo "Usage: /etc/init.d/suricata2mikrotik {start|stop}"
    exit 1
    ;;
esac

exit 0