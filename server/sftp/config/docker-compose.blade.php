version: '3'
services:

  sftp:
    build: ../dockerfiles/sftp
    container_name: dp-sftp
    restart: always
    volumes:
{{ $sftpAppVolumes }}
        - ./users.conf:/etc/sftp/users.conf:ro
        - {{ SERVER_WORKDIR }}/config/fail2ban/jail.local:/etc/fail2ban/jail.local
        - {{ SERVER_WORKDIR }}/config/fail2ban/fail2ban.local:/etc/fail2ban/fail2ban.local
        - {{ SERVER_WORKDIR }}/config/fail2ban/docker-sftp.conf:/etc/fail2ban/filter.d/docker-sftp.conf
        {{ ! sp_is_windows() ? "- /var/log/syslog:/var/log/dockerpilot_syslog" : "" }}
    ports:
        - "2222:22"
    {{ ! sp_is_windows() ? "
    logging:
        driver: syslog" : "" }}
    {{ ! sp_is_windows() ? "privileged: true" : "" }}

networks:
  default:
    external:
      name: dockerpilot
