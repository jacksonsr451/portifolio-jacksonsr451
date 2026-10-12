---
slug: idempotent-apis
title: Projetando operações idempotentes em APIs
description: Um guia prático para decidir onde aplicar idempotência e como tornar repetições seguras.
date: 2026-10-10
status: published
---

## Por que repetições precisam de um contrato

Uma rede pode falhar entre o momento em que o servidor confirma uma alteração e aquele em que o cliente recebe a resposta. Sem conseguir distinguir uma resposta perdida de uma operação que falhou, o cliente pode repetir a requisição. Se a operação não for segura para repetição, isso pode duplicar efeitos colaterais.

Idempotência significa que aplicar a mesma operação mais de uma vez produz o mesmo efeito pretendido que aplicá-la uma única vez. Isso não significa que toda resposta precise ser idêntica byte a byte, nem que todos os métodos HTTP sejam automaticamente seguros em qualquer implementação.

## Defina deliberadamente o limite

Em uma operação de criação com risco relevante de repetição, clientes podem enviar uma chave de idempotência. Associe a chave à identidade autenticada e à operação, valide seu formato e defina por quanto tempo ela será mantida. Armazene uma impressão digital da requisição junto ao resultado para rejeitar a reutilização da chave com outro payload, em vez de aplicá-lo silenciosamente.

O registro da chave e a gravação de negócio devem ser coordenados transacionalmente quando o sistema de persistência permitir. Requisições concorrentes com a mesma chave precisam de uma restrição de unicidade ou mecanismo equivalente de serialização; fazer apenas uma consulta seguida de inserção no código da aplicação deixa uma condição de corrida.

## Torne os resultados observáveis

Registre se uma chave está em processamento ou concluída. Defina o que acontece quando uma operação falha antes do commit e quando há efeitos colaterais downstream. Um outbox ou um contrato de idempotência no serviço downstream pode ser necessário para conectar uma transação de banco de dados a um serviço externo.

Teste requisições duplicadas, payload alterado para a mesma chave, submissões concorrentes e recuperação após falha. Documente no contrato da API o escopo e a expiração da chave, além do comportamento de repetição do cliente. Esses detalhes transformam “repetir com segurança” de uma suposição em um comportamento confiável para quem consome a API.
