FROM gitlab.anakeen.com:4567/customers/docker-images/dev-images/php:ap4-php7.2-pg10
RUN apt update
RUN apt install -y vim
RUN apt install -y iputils-ping
RUN apt install -y bash-completion
RUN echo ". /etc/bash_completion" >> /root/.bashrc

COPY Files/etc/apache2/sites-available/000-anakeen.conf /etc/apache2/sites-available/000-anakeen.conf
#RUN /var/www/html/control/anakeen-control  _completion --generate-hook --shell-type=bash > /etc/bash_completion.d/anakeen-control.bash