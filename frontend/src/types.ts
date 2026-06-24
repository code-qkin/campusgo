export enum ActiveTab {
  Welcome = "welcome",
  Login = "login",
  SignUp = "signup",
  Home = "home",
  Routes = "routes",
  Carpool = "carpool",
  SafeWalk = "safewalk",
  LostFound = "lostfound",
  Rewards = "rewards",
  History = "history",
  Settings = "settings"
}

export interface User {
  fullName: string;
  email: string;
  avatar: string;
  avatarUrl?: string;
  points: number;
}

export interface CampusAlert {
  id: string;
  title: string;
  type: "delay" | "relocation" | "info";
  message: string;
  timeAgo: string;
  timeUnit: string;
  delayValue?: string;
  badgeColor: string;
}

export interface Driver {
  id: string;
  name: string;
  avatar: string;
  rating: number;
  ridesCount: number;
  carModel: string;
  carColor: string;
}

export interface CarpoolRide {
  id: string;
  driver: Driver;
  price: number;
  seatsAvailable: number;
  seatsTotal: number;
  from: string;
  to: string;
  departureTime: string;
  status: "available" | "requested" | "approved";
  preferences: string[];
}

export interface Buddy {
  id: string;
  name: string;
  avatar: string;
  rating: number;
  distance: string;
  major: string;
  isVerified: boolean;
  status: "available" | "inWalk";
  position: { x: number; y: number }; // Percentage position on map grid
}

export interface TrustedContact {
  id: string;
  name: string;
  avatar: string;
  relationship: string;
}

export type ItemCategory = "Electronics" | "Keys" | "Bags" | "Student IDs" | "Others";

export interface LostItem {
  id: string;
  name: string;
  category: ItemCategory;
  location: string;
  timeAgo: string;
  image: string;
  isClaimed: boolean;
  description: string;
}

export interface ActiveReport {
  id: string;
  name: string;
  type: "lost" | "found";
  status: "Reviewing" | "Matched" | "Resolved";
  description: string;
  date: string;
}

export interface RewardItem {
  id: string;
  name: string;
  ptsRequired: number;
  category: string;
  icon: string;
  description: string;
}

export interface HistoryEvent {
  id: string;
  type: "Transit" | "Carpool" | "SafeWalk" | "LostFound" | "Reward";
  title: string;
  detail: string;
  date: string;
  status: string;
  cost?: string;
}
