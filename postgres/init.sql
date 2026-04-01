CREATE TABLE IF NOT EXISTS pastes
(
    id             UUID PRIMARY KEY   DEFAULT uuidv7(),
    encrypted_text TEXT      NOT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT now()
);
