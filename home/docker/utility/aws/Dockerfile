FROM python:2-alpine

RUN apk add --no-cache bash shadow groff

RUN useradd build \
  && pip install awscli

COPY root /

WORKDIR /mount
