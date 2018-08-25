FROM docker.elastic.co/beats/filebeat-oss:6.3.2

USER root

COPY config /usr/share/filebeat

RUN chown -R root /usr/share/filebeat \
  && chmod -R go-w /usr/share/filebeat
