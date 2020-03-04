ARG PROJECT_PHP_VERSION
FROM registry.ap4.anakeen.com/ank-php:$PROJECT_PHP_VERSION

RUN apt-get -y update \
    && apt-get install -y --no-install-recommends \
        vim \
        iputils-ping \
        bash-completion\
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN echo ". /etc/bash_completion" >> /root/.bashrc

#RUN /var/www/html/control/anakeen-control  _completion --generate-hook --shell-type=bash > /etc/bash_completion.d/anakeen-control.bash