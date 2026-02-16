import { router } from '@inertiajs/react';
import { Eye, FileText, Trash2, Upload } from 'lucide-react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import type { SupplierDocument } from '@/types/supplier';

interface DocumentRowProps {
    document: SupplierDocument;
    onUpload: (doc: SupplierDocument) => void;
}

const stateColorMap: Record<string, string> = {
    gray: 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
    blue: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
    green: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
    red: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
};

const borderColorMap: Record<string, string> = {
    gray: 'border-l-gray-300 dark:border-l-gray-600',
    blue: 'border-l-blue-400 dark:border-l-blue-500',
    green: 'border-l-green-400 dark:border-l-green-500',
    red: 'border-l-red-400 dark:border-l-red-500',
};

export default function DocumentRow({
    document: doc,
    onUpload,
}: DocumentRowProps) {
    const color = doc.document_state.color;

    const handleDelete = () => {
        router.delete(`/supplier/documents/${doc.id}`);
    };

    return (
        <div
            className={`flex flex-col gap-3 rounded-lg border border-l-4 p-4 sm:flex-row sm:items-center sm:justify-between ${borderColorMap[color] ?? borderColorMap.gray}`}
        >
            <div className="flex items-start gap-3">
                <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary/20">
                    <FileText className="size-4 text-primary" />
                </div>
                <div className="min-w-0 space-y-1">
                    <p className="text-sm font-medium text-foreground">
                        {doc.document_type.nombre}
                    </p>
                    <div className="flex flex-wrap items-center gap-2">
                        <span
                            className={`inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium ${stateColorMap[color] ?? stateColorMap.gray}`}
                        >
                            {doc.document_state.etiqueta}
                        </span>
                        {doc.archivo_nombre && (
                            <span className="truncate text-xs text-muted-foreground">
                                {doc.archivo_nombre}
                            </span>
                        )}
                    </div>
                    {doc.notas && color === 'red' && (
                        <p className="text-xs text-red-600 dark:text-red-400">
                            Motivo: {doc.notas}
                        </p>
                    )}
                </div>
            </div>
            <div className="flex shrink-0 gap-2 sm:ml-4">
                {doc.has_file && (
                    <Button variant="outline" size="sm" asChild>
                        <a
                            href={`/supplier/documents/${doc.id}/preview`}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <Eye className="size-4" />
                            Ver
                        </a>
                    </Button>
                )}
                {doc.can_upload && (
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => onUpload(doc)}
                    >
                        <Upload className="size-4" />
                        {doc.has_file ? 'Re-subir' : 'Subir'}
                    </Button>
                )}
                {doc.can_delete && (
                    <AlertDialog>
                        <AlertDialogTrigger asChild>
                            <Button variant="ghost" size="sm" className="text-destructive hover:text-destructive">
                                <Trash2 className="size-4" />
                                Eliminar
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    ¿Eliminar documento?
                                </AlertDialogTitle>
                                <AlertDialogDescription>
                                    Se eliminará el archivo &quot;{doc.archivo_nombre}&quot;
                                    de {doc.document_type.nombre}. Podrás subir uno nuevo después.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                <AlertDialogAction onClick={handleDelete} className="bg-destructive text-white hover:bg-destructive/90">
                                    Eliminar
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                )}
            </div>
        </div>
    );
}
