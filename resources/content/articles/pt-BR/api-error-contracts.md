---
slug: api-error-contracts
title: Erros de API fazem parte da interface
description: Respostas de erro consistentes e acionáveis ajudam clientes a se recuperar sem expor detalhes internos.
date: 2026-09-20
status: published
---

## Erros são um contrato público

Quem consome uma API precisa saber se uma requisição pode ser corrigida, repetida ou escalada. Um código de status é um bom ponto de partida, mas o formato da resposta e a estabilidade de seus campos são igualmente importantes.

Use um envelope consistente com um código legível por máquina, um resumo compreensível e, quando apropriado, detalhes de validação por campo. Mantenha esses códigos estáveis mesmo quando as classes internas de exceção ou os detalhes de implementação mudarem.

## Separe as responsabilidades do cliente e do servidor

Erros de validação devem indicar quais entradas podem ser corrigidas sem repetir valores sensíveis. Falhas de autenticação e autorização não devem revelar mais do que a pessoa solicitante pode saber. Falhas inesperadas devem incluir um identificador de correlação que suporte e operadores possam usar para localizar logs protegidos no servidor.

Não retorne stack traces, erros SQL, caminhos do sistema de arquivos, tokens de acesso ou nomes de hosts internos aos clientes. Registre no servidor contexto diagnóstico estruturado, excluindo ou ocultando segredos e dados pessoais.

## Teste o contrato

Teste respostas representativas na fronteira HTTP: status, tipo de conteúdo, campos obrigatórios e ausência de detalhes de implementação. Documente separadamente o comportamento de repetição para limites de taxa, falhas transitórias de serviços upstream e erros permanentes de validação. A consistência permite que clientes implementem uma estratégia de recuperação clara, em vez de deduzirem o comportamento de cada endpoint.
