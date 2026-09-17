# Segurança
- Prepared statements sempre (nunca concatenar SQL).
- Secrets só em .env. Validar/sanitizar toda entrada. Auth em endpoints sensíveis.
- Risco: .env contém senhas em texto plano (DB, FTP, MailJet, WhatsApp, IMAP).
- Risco: WHATSAPP_API_KEY exposta no .env.
- Risco: CRON_SECRET em texto plano.
