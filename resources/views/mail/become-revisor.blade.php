<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuova Richiesta Revisore - Presto.it</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fffdf8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #292929; -webkit-font-smoothing: antialiased;">

    <!-- Wrapper Principale per Centratura Email -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: #fffdf8; padding: 40px 0;">
        <tr>
            <td align="center">
                
                <!-- Container Email (Max 600px) -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border: 1px solid rgba(64, 61, 166, 0.15); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(41, 41, 41, 0.08);">
                    
                    <!-- Top Color Bar (Brand Accent) -->
                    <tr>
                        <td height="6" style="background: linear-gradient(90deg, #00A699, #403DA6, #E63946, #F2BB72);"></td>
                    </tr>

                    <!-- Header con Logo / Titolo -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px; background: linear-gradient(135deg, #fffdf8 0%, #fff4de 100%); border-bottom: 1px solid rgba(41, 41, 41, 0.06);">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <span style="display: inline-block; padding: 6px 14px; background: rgba(230, 57, 70, 0.1); border: 1px solid rgba(230, 57, 70, 0.2); border-radius: 50px; color: #E63946; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px;">
                                            Richiesta Team
                                        </span>
                                        <h1 style="margin: 0; font-family: Georgia, serif; font-size: 28px; font-weight: 900; color: #403DA6; line-height: 1.2;">
                                            Nuovo Candidato Revisore
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Corpo del Messaggio -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 25px 0; font-size: 16px; line-height: 1.6; color: #292929;">
                                Ciao! Un utente ha appena manifestato l'interesse a lavorare con noi e ha richiesto di entrare a far parte del team di revisione su <strong>Presto.it</strong>.
                            </p>

                            <!-- Box Dati Utente -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f0efff; border: 1px solid rgba(64, 61, 166, 0.12); border-radius: 12px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding-bottom: 12px; border-bottom: 1px solid rgba(64, 61, 166, 0.1);">
                                                    <span style="display: block; font-size: 11px; text-transform: uppercase; color: #66646d; font-weight: 700; letter-spacing: 1px; margin-bottom: 4px;">Nome Utente</span>
                                                    <span style="font-size: 18px; font-weight: 800; color: #292929;">{{ $user->name }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 12px;">
                                                    <span style="display: block; font-size: 11px; text-transform: uppercase; color: #66646d; font-weight: 700; letter-spacing: 1px; margin-bottom: 4px;">Indirizzo Email</span>
                                                    <a href="mailto:{{ $user->email }}" style="font-size: 16px; font-weight: 700; color: #00A699; text-decoration: none;">{{ $user->email }}</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 25px 0; font-size: 15px; line-height: 1.6; color: #66646d;">
                                Se desideri verificare il profilo e abilitare i permessi di revisione per questo utente, clicca sul pulsante sottostante:
                            </p>

                            <!-- Pulsante Call to Action -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding: 10px 0 20px 0;">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" bgcolor="#E63946" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);">
                                                    <a href="{{ route('make.revisor', compact('user')) }}" target="_blank" style="font-size: 16px; font-weight: 800; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 8px; border: 1px solid #E63946; display: inline-block; letter-spacing: 0.5px;">
                                                        Rendi Revisore
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer Email -->
                    <tr>
                        <td align="center" style="padding: 25px 40px; background-color: #403DA6; color: #ffffff;">
                            <p style="margin: 0; font-size: 13px; color: rgba(255, 255, 255, 0.8); line-height: 1.5;">
                                Questa è un'email automatica generata da <strong style="color: #F2BB72;">Presto.it</strong>.<br>
                                Non rispondere direttamente a questo messaggio.
                            </p>
                        </td>
                    </tr>

                </table>
                
            </td>
        </tr>
    </table>

</body>
</html>