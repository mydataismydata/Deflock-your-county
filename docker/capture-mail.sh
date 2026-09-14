#!/bin/sh
# Stands in for sendmail inside the container. PHP pipes the whole message here
# on stdin, so the contact form can be tested end to end and you can read what
# it would have delivered.
cat >> /maildrop/sent.eml
printf '\n===8<=== end of message ===8<===\n' >> /maildrop/sent.eml
