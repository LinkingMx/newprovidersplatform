import { Link } from '@inertiajs/react';
import { CheckCircle, ClipboardList, ShieldCheck, User } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { SupplierStatus } from '@/types/supplier';

interface ProgressStepperProps {
    currentStep: number;
    supplierStatus: SupplierStatus;
}

const steps = [
    { label: 'Registro', icon: User },
    { label: 'Perfil', icon: ClipboardList },
    { label: 'Verificación', icon: ShieldCheck },
    { label: 'Activo', icon: CheckCircle },
];

export default function ProgressStepper({
    currentStep,
    supplierStatus,
}: ProgressStepperProps) {
    if (supplierStatus === 'active') {
        return null;
    }

    return (
        <Card className="mb-8">
            <CardHeader>
                <CardTitle className="text-base">
                    Progreso de Activación
                </CardTitle>
                <CardDescription>
                    Completa todos los pasos para activar tu cuenta
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="flex items-center justify-between">
                    {steps.map((step, index) => {
                        const Icon = step.icon;
                        const isCompleted = index < currentStep;
                        const isCurrent = index === currentStep;

                        return (
                            <div
                                key={step.label}
                                className="flex flex-1 items-center"
                            >
                                <div className="flex flex-col items-center gap-2">
                                    <div
                                        className={`flex size-10 items-center justify-center rounded-full border-2 transition-colors sm:size-12 ${
                                            isCompleted
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : isCurrent
                                                  ? 'border-primary bg-primary/10 text-primary'
                                                  : 'border-muted bg-muted text-muted-foreground'
                                        }`}
                                    >
                                        <Icon className="size-4 sm:size-5" />
                                    </div>
                                    <span
                                        className={`text-center text-[10px] font-medium sm:text-xs ${
                                            isCompleted || isCurrent
                                                ? 'text-foreground'
                                                : 'text-muted-foreground'
                                        }`}
                                    >
                                        {step.label}
                                    </span>
                                </div>
                                {index < steps.length - 1 && (
                                    <div
                                        className={`mx-2 h-0.5 flex-1 rounded-full sm:mx-4 ${
                                            isCompleted
                                                ? 'bg-primary'
                                                : 'bg-muted'
                                        }`}
                                    />
                                )}
                            </div>
                        );
                    })}
                </div>

                {supplierStatus === 'registered' && (
                    <div className="mt-6 flex justify-center">
                        <Button asChild size="lg">
                            <Link href="/supplier/onboarding">
                                <ClipboardList className="size-4" />
                                Completar mi Perfil
                            </Link>
                        </Button>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
