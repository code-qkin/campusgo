export type AdminRole = 'super_admin' | 'campus_admin';

export interface AdminUser {
  id: string;
  fullName: string;
  email: string;
  role: AdminRole;
  campusId: string | null;
  campusName: string | null;
}

export interface Campus {
  id: string;
  name: string;
  slug: string;
  emailDomain: string;
  studentCount: number;
  isActive: boolean;
  createdAt: string;
}

export interface StudentRecord {
  id: string;
  fullName: string;
  email: string;
  points: number;
  joinedAt: string;
  isActive: boolean;
}

export interface BusRoute {
  id: string;
  campusId: string;
  name: string;
  color: string;
  stopCount: number;
  isActive: boolean;
}

export interface AdminStats {
  totalStudents: number;
  activeRides: number;
  safeWalkRequests: number;
  lostFoundOpen: number;
  pointsCirculating: number;
  carpoolsToday: number;
}
