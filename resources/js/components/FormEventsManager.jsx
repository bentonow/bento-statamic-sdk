import React, { useState, useEffect } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { ChevronDown } from "lucide-react";
import { cn } from "@/lib/utils";
import { LoaderCircle } from "lucide-react";

const FormEventsManager = () => {
    const [forms, setForms] = useState([]);
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [isCollapsibleOpen, setIsCollapsibleOpen] = useState(false);

    const VISIBLE_ITEMS = 5;
    const visibleForms = forms.slice(0, VISIBLE_ITEMS);
    const collapsibleForms = forms.slice(VISIBLE_ITEMS);
    const hasCollapsibleContent = forms.length > VISIBLE_ITEMS;

    useEffect(() => {
        Promise.all([
            fetch('/cp/bento/forms').then(res => res.json()),
            fetch('/cp/bento/events').then(res => res.json())
        ])
            .then(([formsData, eventsData]) => {
                setForms(formsData);
                setEvents(eventsData);
                setLoading(false);
            })
            .catch(err => {
                setError('Failed to load forms and events');
                setLoading(false);
                // Show error toast
                window.Statamic.$toast.error('Failed to load forms and events');
            });
    }, []);

    const handleEventChange = async (formHandle, eventName) => {
        setError(null);
        try {
            let csrfToken = document.cookie
                .split('; ')
                .find(row => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

            if (csrfToken) {
                csrfToken = decodeURIComponent(csrfToken);
            }

            const response = await fetch(`/cp/bento/forms/${formHandle}/event`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': csrfToken,
                },
                credentials: 'include',
                body: JSON.stringify({
                    event: eventName === "none" ? null : eventName
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to update form event');
            }

            setForms(forms.map(form =>
                form.handle === formHandle
                    ? { ...form, bento_event: eventName === "none" ? null : eventName }
                    : form
            ));

            // Use Statamic's toast system
            window.Statamic.$toast.success('Form event updated successfully');
        } catch (err) {
            console.error('Error updating form event:', err);
            setError(err.message || 'Failed to update form event');
            // Show error toast
            window.Statamic.$toast.error('Failed to update form event');
        }
    };

    if (loading) {
        return (
            <div className="card p-4 mt-4">
                <div className="flex items-center justify-center p-6">
                    <LoaderCircle className="w-6 h-6 animate-spin text-gray-500" />
                </div>
            </div>
        );
    }

    return (
        <div className="card p-4 mt-4">
            <div className="flex justify-between items-center mb-4">
                <h3 className="font-bold text-lg">Form Events</h3>
            </div>

            {error && (
                <Alert variant="destructive" className="mb-4">
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Form</TableHead>
                        <TableHead>Associated Event</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {visibleForms.map((form) => (
                        <TableRow key={form.handle}>
                            <TableCell className="font-medium">{form.title}</TableCell>
                            <TableCell>
                                <Select
                                    value={form.bento_event || "none"}
                                    onValueChange={(value) => handleEventChange(form.handle, value)}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select an event" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">No Event</SelectItem>
                                        {events.map((event) => (
                                            <SelectItem key={event.name} value={event.name}>
                                                {event.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

            {hasCollapsibleContent && (
                <Collapsible
                    open={isCollapsibleOpen}
                    onOpenChange={setIsCollapsibleOpen}
                >
                    <CollapsibleContent>
                        <Table>
                            <TableBody>
                                {collapsibleForms.map((form) => (
                                    <TableRow key={form.handle}>
                                        <TableCell className="font-medium">{form.title}</TableCell>
                                        <TableCell>
                                            <Select
                                                value={form.bento_event || "none"}
                                                onValueChange={(value) => handleEventChange(form.handle, value)}
                                            >
                                                <SelectTrigger className="w-full">
                                                    <SelectValue placeholder="Select an event" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="none">No Event</SelectItem>
                                                    {events.map((event) => (
                                                        <SelectItem key={event.name} value={event.name}>
                                                            {event.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CollapsibleContent>

                    <CollapsibleTrigger className="flex justify-center w-full mt-2">
                        <div className="hover:bg-zinc-100 dark:hover:bg-zinc-800/50 p-2 flex items-center gap-2 text-zinc-400">
                            <ChevronDown
                                className={cn(
                                    "h-4 w-4 transition-transform duration-200",
                                    isCollapsibleOpen && "rotate-180"
                                )}
                            />
                            <span className="text-xs">
                                {isCollapsibleOpen ? "Show Less" : "Show More"}
                            </span>
                        </div>
                    </CollapsibleTrigger>
                </Collapsible>
            )}
        </div>
    );
};

export default FormEventsManager;
