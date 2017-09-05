#!/bin/bash


# $1 : message
log() {
    logger `date +"%b %e %T"` `hostname -s` $0 $1 
    return 0
}


#$1 : login
#$2 : cmde
sulog() {
    status=1

    log "su $1 '$2'"
    su - $1 -c "$2 2>&1 > /tmp/log$$;echo \$? > /tmp/logst$$"
    status=$?
    if [ $status -eq 0 ] ; then
        if [ -f /tmp/logst$$ ]; then
            status=`cat /tmp/logst$$`
            /bin/rm /tmp/logst$$
        fi
    fi
    # log stderr and stdout of child process
    if [ -f /tmp/log$$ ]; then
        log "`cat /tmp/log$$`"
        rm /tmp/log$$
    fi
    return $status
}
