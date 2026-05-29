import { useForm } from '@inertiajs/react';
import { AlertCircle, Upload } from 'lucide-react';
import type { FormEvent } from 'react';
import { useRef } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import type { SupplierDocument } from '@/types/supplier';

const MAX_FILE_SIZE_MB = 25;
const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;

interface UploadDialogProps {
    document: SupplierDocument | null;
    onClose: () => void;
}

export default function UploadDialog({
    document: doc,
    onClose,
}: UploadDialogProps) {
    const fileInputRef = useRef<HTMLInputElement>(null);

    const form = useForm<{ archivo: File | null }>({
        archivo: null,
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        if (!doc || !form.data.archivo) return;

        if (form.data.archivo.size > MAX_FILE_SIZE_BYTES) {
            form.setError(
                'archivo',
                `El archivo supera el tamaño máximo de ${MAX_FILE_SIZE_MB} MB.`,
            );
            return;
        }

        form.post(`/supplier/documents/${doc.id}/upload`, {
            forceFormData: true,
            onSuccess: () => {
                handleClose();
            },
        });
    }

    function handleClose() {
        onClose();
        form.reset();
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    }

    return (
        <Dialog
            open={doc !== null}
            onOpenChange={(open) => {
                if (!open) handleClose();
            }}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {doc?.has_file
                            ? 'Re-subir Documento'
                            : 'Subir Documento'}
                    </DialogTitle>
                    <DialogDescription>
                        {doc?.document_type.nombre}
                    </DialogDescription>
                </DialogHeader>

                {doc?.notas && doc.document_state.color === 'red' && (
                    <Alert variant="destructive">
                        <AlertCircle className="size-4" />
                        <AlertTitle>Documento Rechazado</AlertTitle>
                        <AlertDescription>{doc.notas}</AlertDescription>
                    </Alert>
                )}

                <form onSubmit={handleSubmit}>
                    <div className="space-y-4">
                        <div>
                            <label
                                htmlFor="archivo"
                                className="mb-2 block text-sm font-medium text-foreground"
                            >
                                Archivo
                            </label>
                            <input
                                ref={fileInputRef}
                                id="archivo"
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                className="block w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground file:mr-4 file:rounded-md file:border-0 file:bg-primary/10 file:px-3 file:py-1 file:text-sm file:font-medium file:text-primary hover:file:bg-primary/20"
                                onChange={(e) => {
                                    const file = e.target.files?.[0] ?? null;

                                    if (
                                        file &&
                                        file.size > MAX_FILE_SIZE_BYTES
                                    ) {
                                        form.setError(
                                            'archivo',
                                            `El archivo supera el tamaño máximo de ${MAX_FILE_SIZE_MB} MB.`,
                                        );
                                        form.setData('archivo', null);
                                        if (fileInputRef.current) {
                                            fileInputRef.current.value = '';
                                        }
                                        return;
                                    }

                                    form.clearErrors('archivo');
                                    form.setData('archivo', file);
                                }}
                            />
                            <p className="mt-1 text-xs text-muted-foreground">
                                PDF, JPG o PNG. Tamaño máximo: 25 MB.
                            </p>
                            {form.errors.archivo && (
                                <p className="mt-1 text-xs text-red-600 dark:text-red-400">
                                    {form.errors.archivo}
                                </p>
                            )}
                        </div>
                    </div>

                    <DialogFooter className="mt-6">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleClose}
                            disabled={form.processing}
                        >
                            Cancelar
                        </Button>
                        <Button
                            type="submit"
                            disabled={form.processing || !form.data.archivo}
                        >
                            {form.processing ? (
                                <>
                                    <Spinner />
                                    Subiendo...
                                </>
                            ) : (
                                <>
                                    <Upload className="size-4" />
                                    Subir Documento
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
