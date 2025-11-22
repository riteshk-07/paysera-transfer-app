# Paysera Transfer API

A secure API for transferring funds between accounts, built with Symfony 6.4, PHP 8.2, MySQL, and Redis.

## Installation & Setup

### Prerequisites
- Docker and Docker Compose

### Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd paysera-transfer-app
   ```

2. **Start Docker containers**
   ```bash
   cd docker
   docker-compose up -d
   ```

3. **Install dependencies**
   ```bash
   docker exec -it paysera_composer composer install
   ```

4. **Run database migrations**
   ```bash
   docker exec -it paysera_php php bin/console doctrine:migrations:migrate --no-interaction
   ```

5. **Verify the setup**
   ```bash
   curl http://localhost:8080/debug/repo
   ```

The API will be available at `http://localhost:8080`

## Running the App

### Start the application
```bash
cd docker
docker-compose up -d
```

### Run tests
```bash
docker exec -it paysera_php vendor/bin/phpunit
```

### Common Symfony commands
```bash
# Access PHP container
docker exec -it paysera_php bash

# Clear cache
docker exec -it paysera_php php bin/console cache:clear

# Run migrations
docker exec -it paysera_php php bin/console doctrine:migrations:migrate

# Check routes
docker exec -it paysera_php php bin/console debug:router
```

### API Usage

All endpoints require Bearer token authentication:
```bash
Authorization: Bearer token1
```

> **Note:** The `docker-compose.yml` contains development-only credentials (`token1`, `token2`, `paysera_pass_2025`). These are for local development and testing purposes only. In production, use strong, randomly generated API tokens and secure database credentials.

**Create Account:**
```bash
curl -X POST http://localhost:8080/api/accounts \
  -H "Authorization: Bearer token1" \
  -H "Content-Type: application/json" \
  -d '{"owner_name": "Alice"}'
```

**Create Transfer:**
```bash
curl -X POST http://localhost:8080/api/transfers \
  -H "Authorization: Bearer token1" \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: transfer-123" \
  -d '{
    "from_account_id": "<uuid>",
    "to_account_id": "<uuid>",
    "amount": 50.00
  }'
```

## Time Spent

**Time spent:** ~6-7 hours

This includes:
- Symfony project setup and configuration
- Entity design and database migrations
- Service layer with transaction handling
- API controllers with error handling
- Security implementation (Bearer token authentication)
- Database locking for concurrency control
- Integration tests
- Docker setup and optimization
- Documentation

## AI Tools and Prompts Used

This project was developed with assistance from AI tools (GitHub Copilot, ChatGPT, Cursor AI) for:

**Primary Prompts:**
- "Scan this app. Understand it. This is assignment from fintech company Paysera..."
- "Create a secure API for transferring funds between accounts..."
- "Implement fund transfers between accounts with transaction integrity..."
- "Add pessimistic locking to prevent race conditions..."
- "Create integration tests for transfer scenarios..."

**AI Assistance Used For:**
- Code structure suggestions and Symfony best practices
- Docker configuration optimization
- Test case generation and test environment setup
- Error handling patterns and exception management
- Database transaction and locking strategies
- Idempotency implementation approaches
- Code refactoring and cleanup suggestions

All code has been reviewed, understood, and customized to fit the specific requirements and architecture decisions. The implementation demonstrates production-ready components with proper error handling, security, and reliability features.

## Future Improvements

This implementation focuses on core transfer functionality with production-ready foundations. For a full-scale payment system, the following enhancements would be considered:

1. Full KYC / AML Checks
   - Know Your Customer (KYC) verification workflows
   - Anti-Money Laundering (AML) screening and compliance checks
   - Risk scoring and transaction monitoring
   - Regulatory reporting capabilities

2. Settlement with External Payment Rails
   - Integration with payment networks (SWIFT, SEPA, etc.)
   - Real-time payment processing
   - Settlement reconciliation and reporting
   - Multi-party settlement workflows

3. Multi-currency FX Conversions
   - Real-time foreign exchange rate management
   - Currency conversion with spread handling
   - Multi-currency account support
   - FX risk management and hedging

4. Cross-region DB Replication and Failover
   - Multi-region database replication for high availability
   - Automated failover mechanisms
   - Geographic data distribution for compliance
   - Disaster recovery and backup strategies

These improvements would be implemented based on business requirements, regulatory compliance needs, and scalability demands.
