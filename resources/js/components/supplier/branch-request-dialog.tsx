import { useForm } from '@inertiajs/react';
import { Send } from 'lucide-react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import type { AvailableBranch } from '@/types/supplier';

interface BranchRequestDialogProps {
    open: boolean;
    onClose: () => void;
    availableBranches: AvailableBranch[];
}

export default function BranchRequestDialog({
    open,
    onClose,
    availableBranches,
}: BranchRequestDialogProps) {
    const form = useForm<{
        branch_id: string;
        notas_proveedor: string;
    }>({
        branch_id: '',
        notas_proveedor: '',
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        form.post('/supplier/branch-requests', {
            onSuccess: () => {
                handleClose();
            },
        });
    }

    function handleClose() {
        onClose();
        form.reset();
    }

    return (
        <Dialog
            open={open}
            onOpenChange={(isOpen) => {
                if (!isOpen) handleClose();
            }}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Solicitar Sucursal</DialogTitle>
                    <DialogDescription>
                        Selecciona la sucursal a la que deseas ser asignado. Tu
                        solicitud será revisada por un administrador.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit}>
                    <div className="space-y-4">
                        <div>
                            <Label htmlFor="branch_id">Sucursal</Label>
                            <Select
                                value={form.data.branch_id}
                                onValueChange={(value) =>
                                    form.setData('branch_id', value)
                                }
                            >
                                <SelectTrigger className="mt-1">
                                    <SelectValue placeholder="Selecciona una sucursal" />
                                </SelectTrigger>
                                <SelectContent>
                                    {availableBranches.map((branch) => (
                                        <SelectItem
                                            key={branch.id}
                                            value={String(branch.id)}
                                        >
                                            {branch.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {form.errors.branch_id && (
                                <p className="mt-1 text-xs text-red-600 dark:text-red-400">
                                    {form.errors.branch_id}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="notas_proveedor">
                                Notas (opcional)
                            </Label>
                            <textarea
                                id="notas_proveedor"
                                className="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                                rows={3}
                                maxLength={1000}
                                placeholder="Agrega notas o comentarios sobre tu solicitud..."
                                value={form.data.notas_proveedor}
                                onChange={(e) =>
                                    form.setData(
                                        'notas_proveedor',
                                        e.target.value,
                                    )
                                }
                            />
                            {form.errors.notas_proveedor && (
                                <p className="mt-1 text-xs text-red-600 dark:text-red-400">
                                    {form.errors.notas_proveedor}
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
                            disabled={form.processing || !form.data.branch_id}
                        >
                            {form.processing ? (
                                <>
                                    <Spinner />
                                    Enviando...
                                </>
                            ) : (
                                <>
                                    <Send className="size-4" />
                                    Enviar Solicitud
                                </>
                            )}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
