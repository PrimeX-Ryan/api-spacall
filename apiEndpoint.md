# Wallet API Flow

## 1. Initial Entry
Check if the user exists and is active.
- **POST** `/auth/entry`
- **Body**: `{ "mobile_number": "09123456789" }`
- **Response**: Returns `otp_verification` for new users or `pin_login` for existing.

---

## Path A: New User (Registration)

### 2. Verify OTP
Confirm the code sent to the mobile number.
- **POST** `/auth/verify-otp`
- **Body**: `{ "mobile_number": "09123456789", "otp": "123456" }`

### 3. Register & Set PIN
Complete profile and secure the account.
- **POST** `/auth/register-profile`
- **Body (form-data)**: 
  `first_name`, `last_name`, `gender`, `date_of_birth`, `profile_photo`, `pin` (6 digits), `mobile_number`

---

## Path B: Returning User (Login)

### 2. Login with PIN
- **POST** `/auth/login-pin`
- **Body**: `{ "mobile_number": "09123456789", "pin": "112233" }`

---

## Forgot PIN?

### 1. Request Reset OTP
- **POST** `/auth/forgot-pin`
- **Body (JSON)**:
  ```json
  { "mobile_number": "09123456789" }
  ```

### 2. Reset PIN with OTP
- **POST** `/auth/reset-pin`
- **Body (JSON)**:
  ```json
  {
    "mobile_number": "09123456789",
    "otp": "123456",
    "new_pin": "665544"
  }
  ```
