import { 
  User, 
  CampusAlert, 
  CarpoolRide, 
  Buddy, 
  TrustedContact, 
  LostItem, 
  ActiveReport,
  RewardItem,
  HistoryEvent
} from "./types";

export const initialUser: User = {
  fullName: "Alex",
  email: "alabiabubakr2020@gmail.com",
  avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuCfVWdxa2fT9NyInAQJX4yPYvTFa5_N_7U2Rbm0oQYyS8epqd9CrNxRtrrtU4T9hlDo-vSJhiL8H5t50aewyG4I-gQbYuCvDO-zFPWTTymLef6qFeKjNYhPBCA9jjLhB57-5rliYxbmy-t0gUVrV9y1BynqE4titEJsgd4iwEAF-R1hbcRA2uFNnPWqaiJSjLjDgNeIrcvb5Y1dF1f0T_i50HwAQfpMx4xam8wcnyLLBajD31PIRm-3XFv5eyhbJK6j2iYsy5nnAKI",
  points: 210
};

export const mockAlerts: CampusAlert[] = [
  {
    id: "alert-1",
    title: "Route A Detour",
    type: "delay",
    message: "Due to construction, Campus Loop A is bypassing the Science Center.",
    timeAgo: "10m",
    timeUnit: "ago",
    delayValue: "+10 Min Delay",
    badgeColor: "error"
  },
  {
    id: "alert-2",
    title: "Library Express Added",
    type: "relocation",
    message: "During finals week, we have added an express shuttle to the library from all dorms.",
    timeAgo: "1h",
    timeUnit: "ago",
    badgeColor: "tertiary"
  },
  {
    id: "alert-3",
    title: "SafeWalk High Demand",
    type: "info",
    message: "Current wait times for SafeWalk companions are approximately 15 minutes.",
    timeAgo: "2m",
    timeUnit: "ago",
    badgeColor: "brand-secondary"
  }
];

export const mockCarpoolRides: CarpoolRide[] = [
  {
    id: "ride-1",
    driver: {
      id: "driver-sarah",
      name: "Sarah J.",
      avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuDzCicxkhiPAZ99lHhMycZAbuouXAy20iq6-gfet4k8EyzT2sdvjHIz7JyHd7Lw1LmRtyy3PJdBr3vgRBfH6PjxQXmoegOKEehNPZBVO7mZu-F3b9Mw2bkPmFErvH41iwicpcnCh1TEIF7Sl93__Dda0UGBnCQeVE5KDGy8IYLmSdS5otf49iValxG5ImkH9k7gmEDvT4Pz0XWBxpSvr0m1Hf7QBOzZY01ajvgJw_sZHR7-s_ffrpbWnURENmm35T7AS847XezANXw",
      rating: 4.9,
      ridesCount: 120,
      carModel: "2018 Honda Civic",
      carColor: "Blue"
    },
    price: 12,
    seatsAvailable: 1,
    seatsTotal: 3,
    from: "North Campus Dorms",
    to: "Whole Foods Market (Downtown)",
    departureTime: "Today, 5:30 PM",
    status: "available",
    preferences: ["Music OK", "Light bags only", "No pets"]
  },
  {
    id: "ride-2",
    driver: {
      id: "driver-michael",
      name: "Michael T.",
      avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuBCz-jzrn4ewIl1D54GlcScy-sWDs5hxCIrrhrMGoBYvoaloX6z5EARJWkn6yH9oSyhyVZD9EJUx8K7YPkaZnBB14SbJCzpHFKSEvk38-N-UxCZIVH2XkFAkVARHkop8bodBCtRFPUeZZsDX-RZOtr_HftxIUX2N-rvWmI8jdhHVOjepqYURmUK_Ngdbpec9dFyBL-ec0D_UP8v8EN_rHoCIiq_pdhHWCWYqR-rgakWXhdsYDnSC7WKs7vzgbc5hAPMxZj0UR9amYc",
      rating: 4.7,
      ridesCount: 85,
      carModel: "2020 Toyota Prius",
      carColor: "Silver"
    },
    price: 15,
    seatsAvailable: 2,
    seatsTotal: 4,
    from: "Engineering Building",
    to: "Central Station",
    departureTime: "Today, 6:15 PM",
    status: "available",
    preferences: ["AC Ventilation", "No smoking", "Eco-stats logged"]
  },
  {
    id: "ride-3",
    driver: {
      id: "driver-alex-l",
      name: "Alex L.",
      avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAp2PAc1etVCxsNF7P44rGlsZmZqdPDkNgDOsW_L7iG7eJIb730ms4s-6SZjw8hiTDyXA6bUtEESLdRkgxaqzgfnS_9cgbv1zAT5m8ONDYDQqLOnQeSyNlvQFeIvNopovoS5stdIPZxfgqZUBaKp6JdOXDef7b3UlCM3HRA4cxycshSoDG4wE5TeKQk1fCpmUUgrTnqnyOWv8cb1uD8nR3grw2dKh2rvASLig9lwTcmmycTMvaU6EKZjZmv6Eiqfve89EaZqG6VBQM",
      rating: 5.0,
      ridesCount: 12,
      carModel: "Tesla Model 3",
      carColor: "Black"
    },
    price: 0, // Free carpool as indicated
    seatsAvailable: 1,
    seatsTotal: 1,
    from: "South Library",
    to: "Target (East Side)",
    departureTime: "Tomorrow, 10:00 AM",
    status: "available",
    preferences: ["Quiet Ride", "EV Charging", "Trunk space"]
  }
];

export const mockBuddies: Buddy[] = [
  {
    id: "buddy-1",
    name: "Sarah M.",
    avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuCxnNywTupLlb4F3O2WphH39lKEE59HD4jcS9TBkfZaLZGhZOps7XAGBZlw_jil0cTDVtgx8PyG42ja08ho9nmkrai7iQ-NIC0O_bBSiwaHHBAgichapaKkdlCrTvy_3y52r4HL5voYOSyA3MGT6qFc9-038-hSG6nAXkQ2C9XNPLOV7-yju_eccp6EYhoe-sDmmleFK__ZuzjptAAn7lPwcsOoTv18EDC0byN6RE_W7MKoXepQ60n7qtR0im-Q7oRbR346eUXul54",
    rating: 4.9,
    distance: "200m away",
    isVerified: true,
    major: "Biology Major",
    status: "available",
    position: { x: 68, y: 35 }
  },
  {
    id: "buddy-2",
    name: "David K.",
    avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuCYOiddd-Q_-8aOvhLZnr2pAvsy_sCX5Tqn_Nd2MAx5Jubx_9YESki30MlppTIofHo8TmKFWQAEd24c3LC3Si1PzO7D45lY2IeUZbR44v3EoAeLYh2OVVcSScdQEZ7QtUkS9rHqPTnmRDH9lvgJPmIurA1I8B43jtgbgJ6pl3TrsPzvZuoDvMzCWRo_ckLY-IZom4PRfanudx8gUlU9X0VOKZWVzbEY3hVQUhw0AyjCJV2sJJuaIUuBcSVcp5Fgio0-VoFU0iBtxkE",
    rating: 4.8,
    distance: "450m away",
    isVerified: true,
    major: "Engineering",
    status: "available",
    position: { x: 30, y: 75 }
  },
  {
    id: "buddy-3",
    name: "Jessica L.",
    avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuBBjXMxSSVDQUYpM7NaVPeZ0JJY6HFX-yzKD9W72EI7--V8NPqN8eWAm76ZbxKF1OTGt3DxERhvAS2AK2bp6hvd3zpuVT386lR4N7V8cZAM5u7RgxzZa0zpbIEZ9mCCrnhI2abkZ40gvUgrRWZ69Pidj2mHkuh331r5jxSpzdN0dj-IDJEIH-9tRuWnvGzQcNW9UjuQwcq0J0Gqts6ypzuK36OUYUJmbqZJlOiMEdxFUY-l2SBXxQuBI_62Fin9nPpEBaQpzjZu-t4",
    rating: 5.0,
    distance: "800m away",
    isVerified: false,
    major: "Staff Advisor",
    status: "available",
    position: { x: 25, y: 45 }
  },
  {
    id: "buddy-4",
    name: "Alex J.",
    avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuCFpbhZnaj51ww_gaI3Xr3n9jrBqw9rSiGylv4tMFuzS_WRy27IiycmjR-IlZhkc8XPVYqMdX2S58ISH3ucFcdQLUO9FlVjQM74JHeLD-s1PxKY_GjNXBqq_3ZXhHcct7U1SYGGGcYb05hC3g-0wSysYlLwT9POlK40_-_JjmWl0iHkxtbsoAD2lA8LH6Oz2kZDWGy2vtvsEvnn6Glqsu9h7IsrnV2rS_5HWdLFPxjwTTOqtpjrzxA6WwrMvvdJ0Hkey1NCqmxc028",
    rating: 4.6,
    distance: "In Walk",
    isVerified: false,
    major: "Computer Science",
    status: "inWalk",
    position: { x: 18, y: 22 }
  }
];

export const mockTrustedContacts: TrustedContact[] = [
  {
    id: "contact-1",
    name: "Mom",
    avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuDv61Kz9AeAbblwSgXWcxOM2ijqC1edVqFxIVaNaMtCZksK95S4I7U8aouKFBRx1ZH85J6C_2J64twt5piXLJhkJY-Z0ZeOihsaEKvRxRQ_90FsQfWUcYS2itwmtWyhO5Eq-pwJiO7UgeLGe8BDDRc5rkneDknNLLXxxfOm-V7I-PsGFlBiX2aKOqmNYrWCw6uTYldEWL0v1SKfE7d0fWbHZyg37v-8rhNbzuszxu7He64VdQwDzWi-X7IruKvsyLXSODj70DJRd4o",
    relationship: "Parent"
  },
  {
    id: "contact-2",
    name: "Roommate Clara",
    avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuBBjXMxSSVDQUYpM7NaVPeZ0JJY6HFX-yzKD9W72EI7--V8NPqN8eWAm76ZbxKF1OTGt3DxERhvAS2AK2bp6hvd3zpuVT386lR4N7V8cZAM5u7RgxzZa0zpbIEZ9mCCrnhI2abkZ40gvUgrRWZ69Pidj2mHkuh331r5jxSpzdN0dj-IDJEIH-9tRuWnvGzQcNW9UjuQwcq0J0Gqts6ypzuK36OUYUJmbqZJlOiMEdxFUY-l2SBXxQuBI_62Fin9nPpEBaQpzjZu-t4",
    relationship: "Roommate"
  }
];

export const mockLostItems: LostItem[] = [
  {
    id: "item-1",
    name: "Blue Backpack",
    category: "Bags",
    location: "Shuttle Line B",
    timeAgo: "Today, 10:45 AM",
    image: "https://lh3.googleusercontent.com/aida-public/AB6AXuA8nJ_Byroj-TMmbxfpTMwMxaFJ-Z3fXeXv7AHkKfo9qIwKG9b2lNVDWI-d5f6KZrTLHxB13Kp64yDJW4WolIPhzeBZkTf6JK2-LGdebNQyH5ERiG1wztosWulR7xj5X_AKkIBSCjy8L0kWcpdpbEpq3if10HPVBCoxt1oGOCbV-7bg3iMOcCsrNA85QNLA6gtIzwHk_XrQT5-YvISdcKvBfDkmUIaxrlCXTSbW62mB1pEUQ2hvE7Y3HQjPgaS-4wQ9Y9wgiih4d5w",
    isClaimed: false,
    description: "Contains organic chemistry notes and an orange water flask. Strap has a slight tear on Left hook."
  },
  {
    id: "item-2",
    name: "Silver Macbook",
    category: "Electronics",
    location: "North Campus Dorms",
    timeAgo: "Yesterday, 4:20 PM",
    image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBbW_BiNQxTLCVrSn3zx4TGY-cLJRIcAs16sATMUYIt4jGge1n6KFdqL1cXXSn1iyJpSrFEDVigW8KIxc7TSPIAjHjMRUReaDBSguDgmgVo1o-43vF-lqYUX4x_hNMqpsvFKlq3FJTnJMGrZOTPOXhuwvQxwkFYt9-JrLceQ5lSYSU5oI6Zgt-pYoMVi1ISrmGUoDprYPEiZmGGBu3WCG9muuBY49PLE04p7DC9_rJmqroAFJd7kzXbQxNTyGgGPKTeE1cYmD0Lb1M",
    isClaimed: false,
    description: "Silver 13-inch Macbook Air in grey felt sleeve. Left-bottom corner has a small scratch."
  }
];

export const mockActiveReports: ActiveReport[] = [
  {
    id: "report-1",
    name: "Black Leather Wallet",
    type: "lost",
    status: "Reviewing",
    description: "Lost somewhere near the science building cafeteria around noon. Contains ID and cards.",
    date: "Reported Oct 24"
  },
  {
    id: "report-2",
    name: "Water Bottle (HydroFlask)",
    type: "found",
    status: "Matched",
    description: "Found left on a bench outside the library. Yellow color with stickers.",
    date: "Reported Oct 22"
  }
];

export const mockRewards: RewardItem[] = [
  {
    id: "reward-1",
    name: "Free Coffee",
    ptsRequired: 250,
    category: "Beverage",
    icon: "Coffee",
    description: "Enjoy a free premium espresso or brewed coffee at any campus cafeteria."
  },
  {
    id: "reward-2",
    name: "Shuttle Express Pass",
    ptsRequired: 500,
    category: "Transit",
    icon: "Tickets",
    description: "Priority boarding and lane access on peak-time campus express shuttle routes."
  },
  {
    id: "reward-3",
    name: "CampusGo Premium Hoodie",
    ptsRequired: 1200,
    category: "Eco-Merch",
    icon: "Shirt",
    description: "Our limited edition recycled cotton comfort-weight brand hoodie."
  },
  {
    id: "reward-4",
    name: "Local Diner $15 Voucher",
    ptsRequired: 800,
    category: "Dining",
    icon: "Utensils",
    description: "Get $15 off any dine-in bill at the university-adjacent diner."
  }
];

export const mockHistoryEvents: HistoryEvent[] = [
  {
    id: "hist-1",
    type: "Carpool",
    title: "Eco-Ride with Sarah J.",
    detail: "From North Campus Dorms to Whole Foods",
    date: "Oct 26, 2024",
    status: "Completed",
    cost: "$12.00"
  },
  {
    id: "hist-2",
    type: "SafeWalk",
    title: "SafeWalk escort by David K.",
    detail: "From Library back to Residence building",
    date: "Oct 24, 2024",
    status: "Completed",
    cost: "Free"
  },
  {
    id: "hist-3",
    type: "Reward",
    title: "Claimed Reward: Free Coffee",
    detail: "-250 points applied",
    date: "Oct 22, 2024",
    status: "Redeemed"
  },
  {
    id: "hist-4",
    type: "LostFound",
    title: "Reported Blue Backpack Found",
    detail: "Shuttle Line B drop-off matches",
    date: "Oct 21, 2024",
    status: "Returned"
  }
];
