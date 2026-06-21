export interface Driver {
  id: number;
  fullName: string;
  email: string;
  role: string;
  campusId: string | null;
  campusName: string | null;
}