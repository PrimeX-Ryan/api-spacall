# Spacall API - Comprehensive Handover Documentation

This document serves as the single source of truth for all API endpoints. Use the **Test Token** provided at the bottom for instant authenticated testing.

---

## 🔐 1. Authentication Flow (Unified)
*Used by both Client and Therapist apps.*

### 1.1 Auth Entry
Check if the user exists and decide whether to send OTP or request PIN.
- **POST** `/api/auth/entry`
- **Description**: Verifies the mobile number. Sends SMS OTP if new.
- **Body**:
  ```json
  { "mobile_number": "09123456789" }
  ```
- **Response**: `next_step: "otp_verification"` or `"pin_login"`.

### 1.2 Verify OTP
Verify the code received via SMS.
- **POST** `/api/auth/verify-otp`
- **Description**: Validates the 6-digit code.
- **Body**:
  ```json
  { 
    "mobile_number": "09123456789", 
    "otp": "123456" 
  }
  ```

### 1.3 Register Profile
Create a new user account (Client or Therapist).
- **POST** `/api/auth/register-profile`
- **Description**: Creates the User record. If `role` is `therapist`, it also initializes a professional profile.
- **Body**:
  ```json
  {
      "mobile_number": "09123456789",
      "first_name": "Juan",
      "last_name": "Dela Cruz",
      "gender": "male",
      "date_of_birth": "1990-01-01",
      "pin": "123456",
      "role": "client" // or "therapist"
  }
  ```

### 1.4 Login (PIN)
Authenticate an existing user.
- **POST** `/api/auth/login-pin`
- **Description**: Returns a Bearer Token and the user's role.
- **Body**:
  ```json
  {
      "mobile_number": "09123456789",
      "pin": "123456"
  }
  ```
- **Response Check**: Use `role` (client/therapist) to decide which home screen to show.

---

## 🟢 2. Client App Journey

### 2.1 My Bookings List
- **GET** `/api/bookings`
- **Description**: List all bookings made by the client.

### 2.2 Find Available Therapists
- **GET** `/api/bookings/available-therapists`
- **Parameters**: `latitude`, `longitude`, `radius` (km)
- **Description**: Returns verified, active therapists within the specified range.

### 2.3 Create Immediate Booking
- **POST** `/api/bookings`
- **Description**: Assigns a therapist and creates a booking record.
- **Body**:
  ```json
  {
      "service_id": 3,
      "provider_id": 2,
      "address": "Floor 12, Unit B, Cyber Plaza",
      "latitude": 14.56,
      "longitude": 120.99,
      "city": "Manila",
      "province": "Metro Manila",
      "customer_notes": "Handle with care."
  }
  ```

### 2.4 Track Active Booking
- **GET** `/api/bookings/{id}/track`
- **Description**: Returns live status and therapist GPS coordinates.

### 2.5 Submit Review & Rating
- **POST** `/api/bookings/{id}/reviews`
- **Description**: Rate the service (1-5) after status is `completed`.
- **Body**:
  ```json
  {
      "rating": 5,
      "body": "Excellent service!"
  }
  ```

---

## 🟠 3. Therapist App Journey

### 3.1 Manage My Jobs
- **GET** `/api/bookings`
- **Description**: List all bookings assigned to this therapist.

### 3.2 Update Booking Status
- **PATCH** `/api/bookings/{id}/status`
- **Description**: Progress the job through its lifecycle.
- **Body**:
  ```json
  { "status": "en_route" }
  ```
- **Valid Values**: `accepted`, `en_route`, `arrived`, `in_progress`, `completed`, `cancelled`.

### 3.3 My Professional Profile
- **GET** `/api/therapist/profile`
- **Description**: Returns stats, license info, and specializations.

---

## 🟣 4. Shared Data & Information

### 4.1 Browse Services
- **GET** `/api/services`
- **Description**: List all service categories and individual services.

### 4.2 View Service Details
- **GET** `/api/services/{slug}`
- **Description**: Returns specific pricing and description for a service.

---

## 🛠️ Testing Information
- **Base URL**: `http://localhost:8000/api`
- **Required Headers**:
  - `Authorization: Bearer {token}`
  - `Accept: application/json`
- **Admin/Test Token**: `3|vJLi5np7pDElvBFa103x4deP36YabOdOev3400Tp6bd4be05`
- **Test Therapist Mobile**: `09000000010` (PIN: `123456`)
- **Test Client Mobile**: `09619174255` (PIN: `123456`)
