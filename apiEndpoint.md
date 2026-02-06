# Spacall Wallet API Documentation

---

## 🟦 Authentication Flow

### 1. Initial Entry
Check if the user exists and decide the next step.
- **POST** `/auth/entry`
- **Body**: `{ "mobile_number": "09123456789" }`
- **Sample Response (New User)**:
  ```json
  {
      "message": "OTP sent successfully",
      "next_step": "otp_verification"
  }
  ```
- **Sample Response (Existing User)**:
  ```json
  {
      "message": "User found",
      "next_step": "pin_login"
  }
  ```

---

## 🟢 Client Booking Journey (Book Now)
*Requires Bearer Token in Header*

### Step 1: Discover Available Therapists
Find therapists who are currently online and available.
- **URL**: `GET /bookings/available-therapists`
- **Sample Response**:
  ```json
  {
      "therapists": [
          {
              "id": 1,
              "name": "John Doe",
              "services": [
                  { "id": 1, "name": "Swedish Massage" },
                  { "id": 3, "name": "Organic Facial" }
              ]
          },
          {
              "id": 2,
              "name": "Maria Garcia",
              "services": [
                  { "id": 3, "name": "Organic Facial" },
                  { "id": 5, "name": "Tele-Consultation" }
              ]
          }
      ]
  }
  ```

### Step 2: Immediate Booking
Create a booking for the chosen therapist and service at the client's current location.
- **URL**: `POST /bookings`
- **Body**:
  ```json
  {
      "service_id": 3,
      "provider_id": 2,
      "address": "Unit 102, Green Residence, Taft Ave",
      "latitude": 14.5648,
      "longitude": 120.9932,
      "city": "Manila",
      "province": "Metro Manila",
      "customer_notes": "Ring the doorbell twice."
  }
  ```
- **Response**: Returns the `booking_id` for tracking.

### Step 3: Real-Time Tracking
Track the therapist's status and live location as they head to your location.
- **URL**: `GET /bookings/{id}/track`
- **Sample Response**:
  ```json
  {
      "booking_status": "en_route",
      "therapist_location": { "latitude": 14.5600, "longitude": 120.9900 },
      "eta_minutes": 8
  }
  ```

### Step 4: Feedback (Post-Service)
Rate and review the therapist once the status is `completed`.
- **URL**: `POST /bookings/{id}/reviews`
- **Body**: `{ "rating": 5, "body": "She was very professional and punctual!" }`

---

## 🟠 Therapist Management (Protected)

### 1. Update My Status
Therapists update their progress through the booking.
- **URL**: `PATCH /bookings/{id}/status`
- **Body**: `{ "status": "arrived" }`

### 2. View My Profile
- **URL**: `GET /therapist/profile`

---

## 🟣 Services & Categories
*Requires Bearer Token in Header*

### 1. List All Services
- **URL**: `GET /services`
- **Sample Response**:
  ```json
  {
      "categories": [
          {
              "name": "Massage",
              "services": [
                  { "name": "Swedish Massage", "slug": "swedish-massage" }
              ]
          }
      ]
  }
  ```
