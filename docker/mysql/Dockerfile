FROM mysql:8.0

# Configurações adicionais
RUN echo "default_authentication_plugin=mysql_native_password" >> /etc/mysql/my.cnf

# Copiar scripts de inicialização
COPY ./init/*.sql /docker-entrypoint-initdb.d/

# Copiar configuração personalizada
COPY my.cnf /etc/mysql/conf.d/my.cnf

EXPOSE 3306