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
