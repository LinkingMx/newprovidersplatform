export type SupplierStatus =
    | 'created'
    | 'invited'
    | 'registered'
    | 'profile_completed'
    | 'active';

export interface Branch {
    id: number;
    name: string;
    created_at: string;
}

export interface DocumentType {
    id: number;
    nombre: string;
}

export interface DocumentState {
    id: number;
    nombre: string;
    etiqueta: string;
    color: string;
}

export interface SupplierDocument {
    id: number;
    document_type: DocumentType;
    document_state: DocumentState;
    archivo_nombre: string | null;
    has_file: boolean;
    can_upload: boolean;
    can_delete: boolean;
    notas: string | null;
    uploaded_at: string | null;
}

export type BranchRequestStatus = 'pending' | 'approved' | 'rejected';

export interface BranchRequest {
    id: number;
    branch: {
        id: number;
        name: string;
    };
    status: BranchRequestStatus;
    status_label: string;
    status_color: string;
    notas_proveedor: string | null;
    notas_admin: string | null;
    requested_at: string | null;
    resolved_at: string | null;
}

export interface AvailableBranch {
    id: number;
    name: string;
}

export interface Supplier {
    id: number;
    name: string;
    email: string;
    status: SupplierStatus;
    address_street: string | null;
    address_number: string | null;
    address_neighborhood: string | null;
    address_city: string | null;
    address_zip: string | null;
    address_country: string | null;
    clabe_interbancaria: string | null;
    branches: Branch[];
    created_at: string;
}
