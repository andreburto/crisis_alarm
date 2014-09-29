int left_led = 7;
int right_led = 6;
int button_in = 5;
int button_out = 0;
int alarm = A0;

int alarm_tone = 0;
const int alarm_high = 1000;
const int alarm_low = 1600;

bool flash_on = false;
bool alarm_on = false;
bool yn = true;
int alarm_active = 0;

// Time count - 1000milli = 1sec
unsigned long MLEN = 500;
unsigned long ct = 0;
unsigned long at = 0;

// Other functions used below
void changeTone();
void flashOpposite();
void turnOn();
void turnOff();

// Must pre-define these functions
int setState(String state);

// Setup function
void setup() {
    // Function available from web
    Spark.function("state", setState);
    // Variable from the web
    Spark.variable("alarm_tone", &alarm_tone, INT);
    Spark.variable("alarm_active", &alarm_active, INT);
    // Lights
    pinMode(left_led, OUTPUT);
    digitalWrite(left_led, LOW);
    pinMode(right_led, OUTPUT);
    digitalWrite(right_led, LOW);
    // Button
    pinMode(button_in, INPUT);
    pinMode(button_out, OUTPUT);
    digitalWrite(button_out, LOW);
    // Alarm
    pinMode(alarm, OUTPUT);
}

void loop() {
    // Visual
    if (flash_on == true) {
        if (ct <= millis()) {
            flashOpposite();
        }
    }
    
    // Audio
    if (alarm_on == true) {
        if (at <= millis()) {
            changeTone();
        }
    }
    
    // Check buton
    int btnChk = digitalRead(button_in);
    if (btnChk > 0) { turnOff(); }
}

void changeTone() {
    at = millis() + MLEN;
    
    if (alarm_tone == alarm_high) { alarm_tone = alarm_low; }
    else { alarm_tone = alarm_high; }
    
    analogWrite(alarm, alarm_tone);
}

void flashOpposite() {
    ct = millis() + MLEN;
    
    if (yn == true) {
        yn = false;
        digitalWrite(left_led, HIGH);
        digitalWrite(right_led, LOW);
    } else {
        yn = true;
        digitalWrite(left_led, LOW);
        digitalWrite(right_led, HIGH);
    }
}

void turnOn() {
    alarm_on = true;
    flash_on = true;
    alarm_active = 1;
    digitalWrite(button_out, HIGH);
}

void turnOff() {
    alarm_on = false;
    flash_on = false;
    alarm_active = 0;
    digitalWrite(left_led, LOW);
    digitalWrite(right_led, LOW);
    digitalWrite(button_out, LOW);
    analogWrite(alarm, 0);
}

int setState(String state) {
    if (state.equals("alarm")) {
        turnOn();
        return 1;
    }
    else if (state.equals("off")) {
        turnOff();
        return 0;
    }
    return -1;
}