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

    // Function to fetch events
    const fetchEvents = async () => {
        try {
            const response = await fetch('/cp/bento/events');
            const eventsData = await response.json();
            setEvents(eventsData);

            // Check and update form assignments for removed events
            setForms(prevForms => prevForms.map(form => {
                if (form.bento_event && !eventsData.some(event => event.name === form.bento_event)) {
                    handleEventChange(form.handle, "none", false);
                    return { ...form, bento_event: null };
                }
                return form;
            }));
        } catch (err) {
            console.error('Error fetching events:', err);
            setError('Failed to load events');
        }
    };

    // Function to fetch forms
    const fetchForms = async () => {
        try {
            const response = await fetch('/cp/bento/forms');
            const formsData = await response.json();
            return formsData; // Return the data instead of setting state directly
        } catch (err) {
            console.error('Error fetching forms:', err);
            setError('Failed to load forms');
            return [];
        }
    };

    // Initial data load
    useEffect(() => {
        const loadInitialData = async () => {
            try {
                const [formsData, eventsResponse] = await Promise.all([
                    fetchForms(),
                    fetch('/cp/bento/events').then(res => res.json())
                ]);

                setForms(formsData);
                setEvents(eventsResponse);
                setLoading(false);
            } catch (error) {
                console.error('Error loading initial data:', error);
                setLoading(false);
                window.Statamic.$toast.error('Failed to load forms and events');
            }
        };

        loadInitialData();
    }, []);

    // Set up event listener for Bento events changes
    useEffect(() => {
        const handleBentoEventChange = async () => {
            await fetchEvents(); // Update events
        };

        window.addEventListener('bentoEventsUpdated', handleBentoEventChange);

        return () => {
            window.removeEventListener('bentoEventsUpdated', handleBentoEventChange);
        };
    }, []);

    const handleEventChange = async (formHandle, eventName, showToast = true) => {
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

            setForms(prevForms => prevForms.map(form =>
                form.handle === formHandle
                    ? { ...form, bento_event: eventName === "none" ? null : eventName }
                    : form
            ));

            if (showToast) {
                window.Statamic.$toast.success('Form event updated successfully');
            }
        } catch (err) {
            console.error('Error updating form event:', err);
            setError(err.message || 'Failed to update form event');
            if (showToast) {
                window.Statamic.$toast.error('Failed to update form event');
            }
        }
    };

    if (loading) {
        return (
            <div className="card p-4 mt-4">
                <div className="flex items-center justify-center p-6">
                    <LoaderCircle className="w-6 h-6 animate-spin text-primary" />
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
                                            <SelectItem key={event.id} value={event.name}>
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
                                                        <SelectItem key={event.id} value={event.name}>
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
                        <div className="hover:bg-muted p-2 flex items-center gap-2 text-muted-foreground">
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
