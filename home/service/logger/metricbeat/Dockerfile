FROM docker.elastic.co/beats/metricbeat-oss:6.3.2

COPY config /usr/share/metricbeat
USER root
RUN chmod go-w /usr/share/metricbeat/metricbeat.yml


