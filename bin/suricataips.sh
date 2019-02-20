#! /bin/bash

### BEGIN INIT INFO
# Provides:          suricataips
# Required-Start:    $all
# Required-Stop:     $local_fs
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Suricata service
# Description:       Run Suricata service sudo -u Suricata-user 
### END INIT INFO

# Carry out specific functions when asked to by the system
case "$1" in
  start)
    echo "Starting Suricata..."
   # bash -c 'cd /var/www/html/suricata2ips/bin/ && ./start-Suricata.sh'
    bash -c 'cd /usr/local/bin/ && start_suricata'
    ;;
  stop)
    echo "Stopping Suricata..."
     # bash -c 'cd /var/www/html/suricata2ips/bin/ && ./stop-Suricata.sh'
     bash -c 'pkill trafr'
    sleep 2
    ;;
   restart)
        $0 stop
        sleep 5
        $0 start
    ;;
    
  *)
    echo "Usage: /etc/init.d/suricataips {start|stop|restart}"
    exit 1
    ;;
esac

exit 0