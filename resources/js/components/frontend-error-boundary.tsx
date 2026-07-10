import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/react';
import { AlertTriangle, Home, RefreshCcw, RotateCcw } from 'lucide-react';
import { Component, type ErrorInfo, type ReactNode } from 'react';

type FrontendErrorBoundaryProps = {
    children: ReactNode;
};

type FrontendErrorBoundaryState = {
    error: Error | null;
    errorInfo: ErrorInfo | null;
};

export class FrontendErrorBoundary extends Component<FrontendErrorBoundaryProps, FrontendErrorBoundaryState> {
    private removeSuccessListener?: VoidFunction;

    state: FrontendErrorBoundaryState = {
        error: null,
        errorInfo: null,
    };

    static getDerivedStateFromError(error: Error): Partial<FrontendErrorBoundaryState> {
        return { error };
    }

    componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
        this.setState({ errorInfo });

        if (import.meta.env.DEV) {
            console.error('Frontend error boundary caught an error:', error, errorInfo);
        }
    }

    componentDidMount(): void {
        this.removeSuccessListener = router.on('success', () => {
            if (this.state.error) {
                this.reset();
            }
        });
    }

    componentWillUnmount(): void {
        this.removeSuccessListener?.();
    }

    private reset = () => {
        this.setState({
            error: null,
            errorInfo: null,
        });
    };

    private reload = () => {
        window.location.reload();
    };

    private goDashboard = () => {
        this.reset();
        router.visit('/dashboard');
    };

    render() {
        if (!this.state.error) {
            return this.props.children;
        }

        return (
            <div className="bg-background text-foreground flex min-h-screen items-center justify-center p-4">
                <Card className="w-full max-w-xl overflow-hidden">
                    <CardHeader className="border-b">
                        <div className="flex items-start gap-3">
                            <span className="bg-destructive/10 text-destructive flex size-10 shrink-0 items-center justify-center rounded-lg">
                                <AlertTriangle className="size-5" />
                            </span>
                            <div className="min-w-0">
                                <CardTitle>Terjadi error pada tampilan</CardTitle>
                                <CardDescription className="mt-1">
                                    Komponen halaman gagal dirender. Data kamu tidak otomatis berubah, jadi aman untuk mencoba ulang.
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-5 p-5">
                        <div className="bg-muted/60 rounded-lg border p-4">
                            <p className="text-sm font-medium">{this.state.error.name || 'Frontend Error'}</p>
                            <p className="text-muted-foreground mt-1 text-sm">{this.state.error.message || 'Unknown render error.'}</p>
                        </div>

                        {import.meta.env.DEV && this.state.errorInfo?.componentStack ? (
                            <details className="rounded-lg border">
                                <summary className="cursor-pointer px-4 py-3 text-sm font-medium">Detail developer</summary>
                                <pre className="text-muted-foreground max-h-56 overflow-auto border-t p-4 text-xs whitespace-pre-wrap">
                                    {this.state.error.stack}
                                    {'\n\n'}
                                    {this.state.errorInfo.componentStack}
                                </pre>
                            </details>
                        ) : null}

                        <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <Button type="button" variant="outline" onClick={this.goDashboard}>
                                <Home className="size-4" />
                                Dashboard
                            </Button>
                            <Button type="button" variant="outline" onClick={this.reload}>
                                <RefreshCcw className="size-4" />
                                Reload
                            </Button>
                            <Button type="button" onClick={this.reset}>
                                <RotateCcw className="size-4" />
                                Coba Lagi
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        );
    }
}
